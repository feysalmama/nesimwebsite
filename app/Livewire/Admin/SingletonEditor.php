<?php

namespace App\Livewire\Admin;

use App\Admin\SingletonSpec;
use App\Models\ActivityLog;
use App\Support\AdminNav;
use App\Support\ColumnLimits;
use App\Support\LocaleText;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

/**
 * The four singleton screens — about-content, landing-content,
 * president-message and settings — driven by an App\Admin\SingletonSpec.
 *
 * Each of those React pages was the same three steps: fetch the one row into
 * useState, render labelled inputs over it, PUT the whole object back. There was
 * no shared component to port because they never used ResourceManager, so this
 * is the shared component they should have had.
 *
 * What changed and why:
 *
 *  - The GET routes created the row when it was missing, so opening Settings on
 *    an unseeded database performed a write. This loads the row with
 *    firstOrNew() instead: reading stays read-only, and the INSERT happens on
 *    the first Save with the literal id Prisma used to default it to.
 *  - `toast("Failed to save", "error")` on any non-2xx became real validation
 *    with per-field messages. The PUT routes destructured id/createdAt/updatedAt
 *    off the body and handed the rest straight to Prisma, so the only thing
 *    standing between an editor and a database error was a try/catch.
 *  - Repeaters and image lists drop rows whose fields are all empty before
 *    encoding. The React version appended `{"icon":"","region":"","count":""}`
 *    on "+ Add Region" and stored it as typed, so abandoning a half-filled row
 *    left the home page rendering a blank card — and because a non-empty array
 *    is truthy, it also suppressed HomeController's fallback content.
 *  - The upload goes to Admin\UploadController over fetch, exactly as
 *    ResourceManager does it, so there is still one upload path on the site.
 *  - A field declared localeText or localeTextarea renders one box per language
 *    and writes the three back as the single JSON document the column holds.
 *    The React pages bound one <input> straight over that column, so an editor
 *    opened About Page Content to find {"en":"…","am":"…","om":"…"} in the Hero
 *    Title box — and the first keystroke replaced all three languages with one
 *    plain string. The public pages read these columns through
 *    LocaleText::get(), so the Amharic and Afaan Oromoo site lost its About page
 *    text the moment anyone saved it.
 */
class SingletonEditor extends Component
{
    /** The AdminNav slug, e.g. "settings". The spec is resolved from it. */
    public string $resource = '';

    /**
     * The row as a property bag keyed by column name. Scalars hold a string or
     * null; a repeater holds a list of sub-arrays and an imageList a list of
     * strings, both decoded from their JSON column on load and re-encoded on
     * save.
     *
     * @var array<string, mixed>
     */
    public array $form = [];

    /** The visible tab, for a tabbed spec. Empty when tabbed() is false. */
    public string $tab = '';

    /** The React pages reported upload failures through ImageUpload; this is it. */
    public ?string $uploadError = null;

    /**
     * Resolved per request rather than stored: Livewire dehydrates public
     * properties only, and a section's `subfields` could not survive a round
     * trip alongside the closures a spec is free to declare.
     */
    private ?SingletonSpec $spec = null;

    public function mount(string $resource): void
    {
        $this->resource = $resource;

        $sections = $this->spec()->sections();

        $this->tab = $this->spec()->tabbed() ? (string) ($sections[0]['tab'] ?? '') : '';
        $this->form = $this->formFrom($this->row());
    }

    /* ── Saving ───────────────────────────────────────────────────────────── */

    public function save(): void
    {
        $this->assertRoleAllowed($this->spec()->writeRoles());

        /*
         * Empty becomes null before validating rather than after. Every column
         * these four tables hold is nullable, `nullable` only passes on a real
         * null, and Livewire's update payload never goes through
         * ConvertEmptyStringsToNull — it arrives inside the snapshot's `updates`
         * JSON, not in $request->input(). Without this, clearing the Primary
         * Color text input failed its own regex rule.
         */
        $this->form = $this->normalize($this->form);

        $this->validate($this->validationRules());

        $data = $this->payload();

        $row = $this->row();
        $isNew = ! $row->exists;

        $row->fill($data)->save();

        /*
         * The React routes logged "update" only, and only because the row
         * always existed by then — their GET had just created it. Both actions
         * are distinguished here since nothing creates the row on read now.
         */
        $this->recordActivity($isNew ? 'create' : 'update', (string) $row->getKey());

        // Settings resolves itself once per request for the navbar and footer.
        $model = $this->spec()->model();
        $model::flushResolved();

        $this->uploadError = null;
    }

    /* ── Tabs and collection rows ─────────────────────────────────────────── */

    public function selectTab(string $tab): void
    {
        // Declared tabs only: selectTab() is callable from the browser with any
        // string, and an undeclared one would render an empty screen.
        foreach ($this->spec()->sections() as $section) {
            if (($section['tab'] ?? null) === $tab) {
                $this->tab = $tab;

                return;
            }
        }
    }

    /** "+ Add Region", "+ Add Image" and friends. */
    public function addRow(string $field): void
    {
        $this->assertRoleAllowed($this->spec()->writeRoles());

        $definition = $this->collectionField($field);

        if ($definition === null) {
            return;
        }

        $rows = $this->rows($field);

        if (count($rows) >= (int) ($definition['max'] ?? PHP_INT_MAX)) {
            return;
        }

        $rows[] = $definition['type'] === 'imageList'
            ? ''
            : array_fill_keys(array_column($definition['subfields'], 'name'), '');

        $this->form[$field] = $rows;
        $this->resetErrorBag('form.'.$field);
    }

    public function removeRow(string $field, int $index): void
    {
        $this->assertRoleAllowed($this->spec()->writeRoles());

        if ($this->collectionField($field) === null) {
            return;
        }

        $rows = $this->rows($field);

        if (! array_key_exists($index, $rows)) {
            return;
        }

        unset($rows[$index]);

        // Re-keyed: the view binds form.field.0, form.field.1, ... and a gap
        // would leave Livewire writing to an index that no longer exists.
        $this->form[$field] = array_values($rows);
    }

    /* ── Upload result ────────────────────────────────────────────────────── */

    /**
     * Called by the handler in the component view once Admin\UploadController
     * has answered with {"url": "/uploads/..."}.
     *
     * $row and $sub place the URL: both null for a top-level image field, $row
     * alone for a row of an imageList, both for a sub-field inside a repeater
     * row. Every argument comes from the browser, so all three are checked
     * against the spec's own declarations before anything is written.
     */
    public function setMediaUrl(string $field, string $url, ?int $row = null, ?string $sub = null): void
    {
        $this->assertRoleAllowed($this->spec()->writeRoles());

        $definition = $this->fieldByName($field);

        if ($definition === null) {
            return;
        }

        // Which of the three placements this field can accept.
        $allowed = match ($definition['type']) {
            'image' => $row === null && $sub === null,
            'imageList' => $row !== null && $sub === null,
            'repeater' => $row !== null && $sub !== null && $this->subfieldIsImage($definition, $sub),
            default => false,
        };

        if (! $allowed) {
            return;
        }

        $url = trim($url);

        // Same rule ResourceManager applies: a local upload path, or an http(s)
        // address pasted in by hand. Nothing else — no javascript:, no
        // protocol-relative //host, no path outside /uploads.
        $isUpload = str_starts_with($url, '/uploads/') && ! str_contains($url, '..');
        $isRemote = filter_var($url, FILTER_VALIDATE_URL) !== false
            && in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true);

        if (! $isUpload && ! $isRemote) {
            $this->uploadError = 'That address was rejected. Upload a file instead.';

            return;
        }

        $this->uploadError = null;

        $rows = $this->rows($field);

        if ($row !== null && ! array_key_exists($row, $rows)) {
            return;
        }

        if ($definition['type'] === 'image') {
            $this->form[$field] = $url;
        } elseif ($definition['type'] === 'imageList') {
            $rows[$row] = $url;
            $this->form[$field] = $rows;
        } else {
            $rows[$row][$sub] = $url;
            $this->form[$field] = $rows;
        }

        $this->resetErrorBag('form.'.$field);
    }

    /** The failure branch of the same handler. */
    public function uploadFailed(string $message): void
    {
        $this->uploadError = $message === '' ? 'Upload failed.' : Str::limit($message, 200);
    }

    /* ── Shape of the module, for the view ────────────────────────────────── */

    /**
     * The declared sections with every optional key filled in and every field
     * normalised, so the view reads $section['layout'] and $field['span']
     * directly instead of repeating `?? …` once per field type.
     *
     * @return array<int, array{tab: ?string, title: string, description: ?string, layout: string, fields: array<int, array<string, mixed>>}>
     */
    public function sections(): array
    {
        return array_map(
            fn (array $section) => [
                'tab' => null,
                'description' => null,
                'layout' => 'stack',
                ...$section,
                'fields' => array_map(
                    fn (array $field) => $this->normalizeField($field, $section['layout'] ?? 'stack'),
                    $section['fields'] ?? [],
                ),
            ],
            $this->spec()->sections(),
        );
    }

    /**
     * A section's fields split into what the view renders: loose fields and the
     * bordered boxes that gather the ones sharing a `group` key.
     *
     * Grouping happens here rather than in Blade because "close the box when the
     * next field's group differs" is a stateful loop, and Blade has nowhere to
     * hold that state without an @php block in the middle of the markup.
     *
     * @return array<int, array{kind: string, title?: string, field?: array<string, mixed>, fields?: array<int, array<string, mixed>>}>
     */
    public function blocks(array $section): array
    {
        $blocks = [];
        $open = null;

        foreach ($section['fields'] as $field) {
            if ($field['group'] === null) {
                $blocks[] = ['kind' => 'field', 'field' => $field];
                $open = null;

                continue;
            }

            if ($open === null || $blocks[$open]['title'] !== $field['group']) {
                $blocks[] = ['kind' => 'group', 'title' => $field['group'], 'fields' => []];
                $open = array_key_last($blocks);
            }

            $blocks[$open]['fields'][] = $field;
        }

        return $blocks;
    }

    /**
     * Lets the view hide the Save button a read-only role would only be met with
     * a 403 for. The React pages always showed it and let the PUT refuse.
     */
    public function canWrite(): bool
    {
        $user = auth()->user();

        if ($user === null || ! $user->isStaff()) {
            return false;
        }

        $roles = $this->spec()->writeRoles();

        return $roles === null || in_array($user->role, $roles, true);
    }

    /**
     * Without these, messages read "The form.impactQuoteAuthor field is
     * required." HandlesValidation picks the method up on its own.
     *
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        $attributes = [];

        foreach ($this->fields() as $field) {
            $label = Str::lower($field['label']);

            $attributes['form.'.$field['name']] = $label;

            // A locale field reports against form.name.en, not form.name.
            if ($this->isLocale($field)) {
                foreach (LocaleText::LOCALES as $locale) {
                    $attributes['form.'.$field['name'].'.'.$locale] = $label.' ('.LocaleText::LABELS[$locale].')';
                }
            }
        }

        return $attributes;
    }

    public function render(): View
    {
        /*
         * Checked in render() and not only in mount(): render() runs on the page
         * load and on every subsequent update, so a replayed snapshot cannot
         * read — or write — a module the signed-in role was never allowed to
         * open. Same reasoning as ResourceManager::render().
         */
        $this->assertRoleAllowed($this->spec()->readRoles());

        return view('livewire.admin.singleton-editor', [
            'spec' => $this->spec(),
            'sections' => $this->sections(),
            // For the locale branch of the field partial, which @include passes
            // down with the rest of the view data.
            'locales' => LocaleText::LOCALES,
            'localeLabels' => LocaleText::LABELS,
            'canWrite' => $this->canWrite(),
        ]);
    }

    /* ── Internals ────────────────────────────────────────────────────────── */

    private function spec(): SingletonSpec
    {
        if ($this->spec === null) {
            $class = AdminNav::spec($this->resource);

            /*
             * Also fires when a slug is registered against the wrong kind of
             * spec, which is the mistake worth failing loudly on: the route
             * would render and the component would then have no idea what to do
             * with the class it was given.
             */
            abort_if($class === null || ! is_subclass_of($class, SingletonSpec::class), 404);

            $this->spec = new $class;
        }

        return $this->spec;
    }

    /**
     * The one row, or an unsaved instance carrying its literal id so the first
     * Save inserts. The React GET routes created it on read; this does not.
     */
    private function row(): Model
    {
        $model = $this->spec()->model();

        $row = $model::query()->firstOrNew(['id' => $this->spec()->rowId()]);

        if (! $row->exists) {
            $row->forceFill($this->spec()->fallbacks());
        }

        return $row;
    }

    /**
     * Every declared field, normalised. Kept separate from sections() because
     * save(), setMediaUrl() and validationAttributes() all want the flat list
     * and none of them care how the fields were grouped on screen.
     *
     * @return array<int, array<string, mixed>>
     */
    private function fields(): array
    {
        $fields = [];

        foreach ($this->sections() as $section) {
            foreach ($section['fields'] as $field) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * @param  array<string, mixed>  $field
     * @return array<string, mixed>
     */
    private function normalizeField(array $field, string $layout): array
    {
        $type = (string) ($field['type'] ?? 'text');

        /*
         * A collection is always full width: it is a box of its own rows. So is
         * a locale field, which is a box of three of them.
         */
        $wide = in_array($type, ['textarea', 'json', 'repeater', 'imageList', 'localeText', 'localeTextarea'], true);

        return [
            'type' => 'text',
            'required' => false,
            'rows' => 3,
            'placeholder' => null,
            'hint' => null,
            'group' => null,
            'max' => 50,
            'addLabel' => '+ Add',
            'rowLabel' => 'Item',
            'inline' => false,
            'subfields' => [],
            ...$field,
            'span' => $field['span'] ?? ($wide || $layout !== 'grid' ? 'full' : 'half'),
            'subfields' => array_map(
                static fn (array $sub) => [
                    'type' => 'text',
                    'rows' => 2,
                    'placeholder' => null,
                    'span' => 'full',
                    ...$sub,
                ],
                $field['subfields'] ?? [],
            ),
        ];
    }

    /**
     * @param  array<string, mixed>  $form
     * @return array<string, mixed>
     */
    private function normalize(array $form): array
    {
        foreach ($this->fields() as $field) {
            $name = $field['name'];
            $value = $form[$name] ?? null;

            if ($this->isLocale($field)) {
                /*
                 * Left as the three-language map. The "" -> null rule below is
                 * about a scalar column, and these strings sit one level down
                 * where it does not reach; nulling the whole map here would hand
                 * encodeLocale() nothing to read.
                 *
                 * A scalar arriving in its place is repaired through parse(),
                 * which is also how a column still holding plain text — written
                 * by the React input this replaces — lands in the English box
                 * instead of being thrown away.
                 */
                $form[$name] = is_array($value) ? $value : LocaleText::parse(is_string($value) ? $value : null);

                continue;
            }

            if (in_array($field['type'], ['repeater', 'imageList'], true)) {
                $form[$name] = is_array($value) ? $value : [];

                continue;
            }

            $form[$name] = is_string($value) && trim($value) === '' ? null : $value;
        }

        return $form;
    }

    /**
     * Coerce the validated values into what the columns actually hold — the
     * body of each React page's handleSave(), minus the JSON.stringify(data)
     * that sent columns this screen never showed.
     *
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        $data = [];

        foreach ($this->fields() as $field) {
            $name = $field['name'];
            $value = $this->form[$name] ?? null;

            $data[$name] = match ($field['type']) {
                'repeater' => $this->encodeRows($value, $field),
                'imageList' => $this->encodeList($value),
                'localeText', 'localeTextarea' => $this->encodeLocale($value),
                default => $value,
            };
        }

        return $data;
    }

    /**
     * A repeater column: keep the declared sub-fields, as strings, and drop rows
     * with nothing in them. Encoded with the same flags LocaleText::encode uses
     * so the emoji these fields are full of survive as UTF-8 rather than as
     * \uXXXX escapes in a column an editor may one day read raw.
     *
     * @return string|null
     */
    private function encodeRows(mixed $rows, array $field): ?string
    {
        if (! is_array($rows)) {
            return null;
        }

        $names = array_column($field['subfields'], 'name');
        $kept = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $clean = [];

            foreach ($names as $key) {
                $clean[$key] = trim((string) ($row[$key] ?? ''));
            }

            if (implode('', $clean) === '') {
                continue;
            }

            $kept[] = $clean;
        }

        return (string) json_encode($kept, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * The three languages of a locale field back into the one JSON document the
     * column holds — the shape BaseModel::text() resolves on the public site.
     *
     * An all-blank map still encodes to {"en":"","am":"","om":""} rather than to
     * null, which is what ResourceManager writes for the same field type; every
     * read path falls through that with `?:` exactly as it falls through a null.
     */
    private function encodeLocale(mixed $value): string
    {
        $map = is_array($value) ? $value : [];

        return LocaleText::encode(
            (string) ($map['en'] ?? ''),
            (string) ($map['am'] ?? ''),
            (string) ($map['om'] ?? ''),
        );
    }

    /** An imageList column: the same, over bare strings. */
    private function encodeList(mixed $rows): ?string
    {
        if (! is_array($rows)) {
            return null;
        }

        $kept = [];

        foreach ($rows as $url) {
            $url = trim((string) $url);

            if ($url !== '') {
                $kept[] = $url;
            }
        }

        return (string) json_encode($kept, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Length ceilings come from the columns themselves rather than being
     * written down here, for the reason given on App\Support\ColumnLimits.
     *
     * @return array<string, array<int, mixed>>
     */
    private function validationRules(): array
    {
        $rules = [];
        $model = $this->spec()->model();
        $table = (new $model)->getTable();

        foreach ($this->fields() as $field) {
            $key = 'form.'.$field['name'];
            $limit = ColumnLimits::get($table, $field['name']);

            if ($this->isLocale($field)) {
                $rules[$key] = ['required', 'array'];

                /*
                 * One column holds all three languages plus the JSON punctuation
                 * around them, so a third of it is what any single language can
                 * have. The same arithmetic ResourceManager uses, for the same
                 * reason: bounding each locale by the whole width would let a
                 * save through that the column cannot store.
                 */
                $perLocale = max(1, intdiv($limit, 3) - 12);

                foreach (LocaleText::LOCALES as $locale) {
                    $rules[$key.'.'.$locale] = [
                        // Only English is required, so a page can be published
                        // before its translations exist.
                        $field['required'] && $locale === LocaleText::DEFAULT_LOCALE ? 'required' : 'nullable',
                        'string',
                        'max:'.$perLocale,
                    ];
                }

                continue;
            }

            $rules[$key] = match ($field['type']) {
                'repeater', 'imageList' => ['array', 'max:'.$field['max'], $this->encodedFits($limit)],
                'json' => ['nullable', 'json', 'max:'.$limit],
                // A colour arrives from either the swatch or the hex input;
                // SettingsSpec::rules() carries the format check.
                'color' => ['nullable', 'string', 'max:'.$limit],
                default => [$field['required'] ? 'required' : 'nullable', 'string', 'max:'.$limit],
            };
        }

        return array_merge($rules, $this->spec()->rules());
    }

    /**
     * `max:` on an array counts rows, but what the column has to hold is the
     * encoded document — so a repeater needs a second rule bounding that, or a
     * list of rows each within its own limits can still overflow the column.
     */
    private function encodedFits(int $limit): Closure
    {
        return static function (string $attribute, mixed $value, Closure $fail) use ($limit): void {
            $rows = is_array($value) ? array_values($value) : [];
            $encoded = (string) json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            if (mb_strlen($encoded) > $limit) {
                $fail('This list is too long to store — shorten its entries or remove a row.');
            }
        };
    }

    /**
     * The row list for a collection field as it sits in $form, always a
     * 0-indexed array of the right shape. Tolerates a Livewire update that left
     * a gap in the keys, which is why callers re-index with array_values().
     *
     * Public because the field partial loops over it: an imageList's and a
     * repeater's rows are only knowable here, and $form could hold a stale gap
     * between removeRow() re-keying it and the next update arriving.
     *
     * @return array<int, mixed>
     */
    public function rows(string $field): array
    {
        $rows = $this->form[$field] ?? [];

        return is_array($rows) ? array_values($rows) : [];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fieldByName(string $name): ?array
    {
        foreach ($this->fields() as $field) {
            if ($field['name'] === $name) {
                return $field;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $field
     */
    private function isLocale(array $field): bool
    {
        return in_array($field['type'], ['localeText', 'localeTextarea'], true);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function collectionField(string $name): ?array
    {
        $field = $this->fieldByName($name);

        if ($field === null || ! in_array($field['type'], ['repeater', 'imageList'], true)) {
            return null;
        }

        return $field;
    }

    /**
     * @param  array<string, mixed>  $field
     */
    private function subfieldIsImage(array $field, string $sub): bool
    {
        foreach ($field['subfields'] as $candidate) {
            if ($candidate['name'] === $sub) {
                return $candidate['type'] === 'image';
            }
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    private function formFrom(Model $row): array
    {
        $values = [];

        foreach ($this->fields() as $field) {
            $raw = $row->getAttribute($field['name']);

            $values[$field['name']] = match ($field['type']) {
                'repeater' => $this->rowsFrom(is_string($raw) ? $raw : null, $field),
                'imageList' => $this->listFrom(is_string($raw) ? $raw : null),
                'localeText', 'localeTextarea' => LocaleText::parse(is_string($raw) ? $raw : null),
                default => $raw === null ? '' : (string) $raw,
            };
        }

        return $values;
    }

    /**
     * Decode a repeater column into rows carrying every declared sub-field, so
     * a row written before a sub-field was added still renders an input for it
     * instead of leaving the view reading an undefined key.
     *
     * Rows that are not arrays are dropped: the React parseJson() returned
     * whatever JSON.parse produced, and a column holding `["a","b"]` under an
     * object repeater used to render as two broken cards.
     *
     * @return array<int, array<string, string>>
     */
    private function rowsFrom(?string $raw, array $field): array
    {
        $names = array_column($field['subfields'], 'name');
        $rows = [];

        foreach (LocaleText::json($raw) as $item) {
            if (! is_array($item)) {
                continue;
            }

            $row = [];

            foreach ($names as $name) {
                $value = $item[$name] ?? '';

                $row[$name] = is_scalar($value) ? (string) $value : '';
            }

            $rows[] = $row;
        }

        return $rows;
    }

    /** @return array<int, string> */
    private function listFrom(?string $raw): array
    {
        $urls = [];

        foreach (LocaleText::json($raw) as $item) {
            if (is_scalar($item)) {
                $urls[] = (string) $item;
            }
        }

        return $urls;
    }

    /**
     * requireRole("SUPER_ADMIN", "CONTENT_ADMIN") from lib/adminAuth.ts, which
     * is what all four singleton PUT routes used; a null list is the
     * requireRole() their GETs used, meaning any of the five staff roles.
     *
     * Not named authorize(): Component pulls in AuthorizesRequests, which owns
     * that name for Gate checks.
     *
     * @param  array<int, string>|null  $roles
     */
    private function assertRoleAllowed(?array $roles): void
    {
        $user = auth()->user();

        abort_if($user === null || ! $user->isStaff(), 403);

        if ($roles !== null) {
            abort_unless(in_array($user->role, $roles, true), 403);
        }
    }

    /**
     * logActivity() from lib/activityLog.ts, with the same empty-catch
     * trade-off the React routes made: an audit row must never fail an editor's
     * save, but it must not disappear without a trace either.
     */
    private function recordActivity(string $action, ?string $entityId): void
    {
        try {
            ActivityLog::create([
                'userId' => auth()->id(),
                'action' => $action,
                'entity' => $this->spec()->entity(),
                'entityId' => $entityId,
            ]);
        } catch (\Throwable $e) {
            Log::error('[admin] activity log write failed', [
                'action' => $action,
                'entity' => $this->spec()->entity(),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
