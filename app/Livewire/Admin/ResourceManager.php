<?php

namespace App\Livewire\Admin;

use App\Admin\ResourceSpec;
use App\Models\ActivityLog;
use App\Support\AdminNav;
use App\Support\ColumnLimits;
use App\Support\LocaleText;
use App\Support\Slug;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

/**
 * components/admin/ResourceManager.tsx and the ResourceForm it opened, ported
 * as a single Livewire component driven by an App\Admin\ResourceSpec.
 *
 * What changed and why:
 *
 *  - The React component made four separate network calls per edit cycle: GET
 *    the list, POST the upload, PUT the row, GET the list again. Here the list,
 *    the form and the row are one component instance, so saving re-renders the
 *    table without a refetch and the stale-after-save behaviour is gone.
 *  - `alert("Failed to save. Check required fields.")` became real validation
 *    with per-field messages. The old form had no server-side checks at all —
 *    lib/crudRoute.ts passed the request body straight to Prisma.
 *  - `confirm("Delete this item?...")` became wire:confirm, same wording.
 *  - The upload still goes to Admin\UploadController over fetch rather than
 *    through Livewire's own temporary-file machinery, so there is one upload
 *    path on the site, one set of MIME and size checks, and every file keeps
 *    getting its Media Library row.
 */
class ResourceManager extends Component
{
    /** The AdminNav slug, e.g. "hero-slides". The spec is resolved from it. */
    public string $resource = '';

    /** Open-form values keyed by field name; locale fields hold {en, am, om}. */
    public array $form = [];

    /** The row being edited, or null while creating. */
    public ?string $editingId = null;

    public bool $showForm = false;

    /** The React form reported upload failures with alert(); this replaces it. */
    public ?string $uploadError = null;

    /**
     * Resolved per request instead of stored. Livewire dehydrates public
     * properties only, so a spec object could not survive a round trip — and a
     * column's `render` closure could not be serialised at all.
     */
    private ?ResourceSpec $spec = null;

    public function mount(string $resource): void
    {
        $this->resource = $resource;
    }

    /* ── Opening and closing the form ─────────────────────────────────────── */

    public function openCreate(): void
    {
        $this->editingId = null;
        $this->form = $this->blankForm();
        $this->uploadError = null;
        $this->showForm = true;

        // Otherwise the messages from an abandoned attempt greet the next one.
        $this->resetErrorBag();
    }

    public function openEdit(string $id): void
    {
        $item = $this->newQuery()->findOrFail($id);

        $this->editingId = (string) $item->getKey();
        $this->form = $this->formFrom($item);
        $this->uploadError = null;
        $this->showForm = true;
        $this->resetErrorBag();
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->editingId = null;
        $this->form = [];
        $this->uploadError = null;
        $this->resetErrorBag();
    }

    /* ── Saving and deleting ──────────────────────────────────────────────── */

    public function save(): void
    {
        $this->assertRoleAllowed($this->spec()->writeRoles());

        $validated = $this->validate($this->validationRules());

        /*
         * Built from the validated data rather than from $this->form: every
         * model in this app has an empty $guarded (see BaseModel), so writing
         * the property bag directly would let a hand-crafted Livewire request
         * set columns no spec ever declared.
         */
        $data = $this->payload($validated['form'] ?? []);

        $model = $this->spec()->model();

        if ($this->editingId !== null) {
            $item = $model::query()->findOrFail($this->editingId);
            $item->update($data);

            $this->recordActivity('update', $this->editingId);
        } else {
            $item = $model::query()->create($data);

            $this->recordActivity('create', (string) $item->getKey());
        }

        $this->closeForm();
    }

    public function delete(string $id): void
    {
        $this->assertRoleAllowed($this->spec()->writeRoles());

        $model = $this->spec()->model();

        $model::query()->findOrFail($id)->delete();

        $this->recordActivity('delete', $id);
    }

    /* ── Upload result ────────────────────────────────────────────────────── */

    /**
     * Called by the handler in the component view once Admin\UploadController
     * has answered with {"url": "/uploads/..."}.
     *
     * Both checks below are load-bearing. Every public method on a Livewire
     * component is callable from the browser with arguments of the caller's
     * choosing, so without them any signed-in editor could write any string
     * into any form key — including one that is not an image field at all.
     */
    public function setMediaUrl(string $field, string $url): void
    {
        $this->assertRoleAllowed($this->spec()->writeRoles());

        $definition = null;

        foreach ($this->fields() as $candidate) {
            if ($candidate['name'] === $field) {
                $definition = $candidate;
            }
        }

        if ($definition === null || ! in_array($definition['type'], ['image', 'video'], true)) {
            return;
        }

        $url = trim($url);

        // A local upload path, or an http(s) address the editor pasted in.
        // Anything else — a scheme like javascript:, a protocol-relative //host,
        // a path outside /uploads — is refused.
        $isUpload = str_starts_with($url, '/uploads/') && ! str_contains($url, '..');
        $isRemote = filter_var($url, FILTER_VALIDATE_URL) !== false
            && in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true);

        if (! $isUpload && ! $isRemote) {
            $this->uploadError = 'That address was rejected. Upload a file instead.';

            return;
        }

        $this->uploadError = null;
        $this->form[$field] = $url;

        // A required image that was empty a moment ago is filled now, and the
        // message from the last failed save should not outlive it.
        $this->resetErrorBag('form.'.$field);
    }

    /** The failure branch of the same handler. */
    public function uploadFailed(string $message): void
    {
        $this->uploadError = $message === '' ? 'Upload failed.' : Str::limit($message, 200);
    }

    /* ── Shape of the module, for the view ────────────────────────────────── */

    /**
     * The spec's field list with every optional key filled in, so the view can
     * read $field['required'] directly instead of repeating `?? false` once per
     * field type. At most a dozen entries, rebuilt each render.
     *
     * @return array<int, array{name: string, label: string, type: string, required: bool, default: mixed, options: array<int|string, mixed>|Closure}>
     */
    public function fields(): array
    {
        return array_map(
            static fn (array $field) => [
                'required' => false,
                'default' => null,
                'options' => [],
                ...$field,
            ],
            $this->spec()->fields(),
        );
    }

    /**
     * A select field's choices as [value => label].
     *
     * The React form either fetched them from `optionsUrl` or mapped an array
     * that could mix bare strings with {value, label} objects. There is no
     * second request to make here, so a spec passes the array — or a closure
     * returning one, when the choices come from another table.
     *
     * @return array<int|string, string>
     */
    public function optionsFor(array $field): array
    {
        $options = $field['options'] instanceof Closure
            ? ($field['options'])()
            : $field['options'];

        $resolved = [];

        foreach ($options as $value => $label) {
            if (is_array($label)) {
                $resolved[(string) ($label['value'] ?? $value)] = (string) ($label['label'] ?? '');
            } elseif (is_int($value)) {
                // A bare list of strings: the value is its own label.
                $resolved[(string) $label] = (string) $label;
            } else {
                $resolved[(string) $value] = (string) $label;
            }
        }

        return $resolved;
    }

    /**
     * @return Collection<int, Model>
     */
    public function items(): Collection
    {
        return $this->newQuery()->get();
    }

    /**
     * Lets the view hide the buttons a read-only role would only be met with a
     * 403 for. The React panel always showed them and let the API refuse.
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
     * One table cell: `String(item[c.key] ?? "")` from the React table, or the
     * column's render callback when it declared one.
     *
     * A boolean with no render becomes Yes/No rather than PHP's "1". Every
     * boolean column in the old panel supplied a render that said exactly that,
     * so this only changes what a spec that forgets one would show.
     *
     * @param  array{key: string, label: string, render?: Closure}  $column
     */
    public function cell(array $column, Model $item): string
    {
        if (isset($column['render'])) {
            return (string) ($column['render'])($item);
        }

        $value = $item->getAttribute($column['key']);

        return match (true) {
            $value === null => '',
            is_bool($value) => $value ? 'Yes' : 'No',
            default => (string) $value,
        };
    }

    /**
     * Without these, messages read "The form.imageUrl field is required."
     * Livewire picks the method up on its own — HandlesValidation calls
     * getValidationAttributes() whenever validate() is given no attributes.
     *
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        $attributes = [];

        foreach ($this->fields() as $field) {
            $label = Str::lower($field['label']);

            $attributes['form.'.$field['name']] = $label;

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
         * Checked here rather than only in mount(): render() runs on the page
         * load and on every subsequent update, so a replayed Livewire snapshot
         * cannot read a module the signed-in role was never allowed to open.
         */
        $this->assertRoleAllowed($this->spec()->readRoles());

        return view('livewire.admin.resource-manager', [
            'spec' => $this->spec(),
            'fields' => $this->fields(),
            'columns' => $this->spec()->columns(),
            'items' => $this->items(),
            'locales' => LocaleText::LOCALES,
            'canWrite' => $this->canWrite(),
        ]);
    }

    /* ── Internals ────────────────────────────────────────────────────────── */

    private function spec(): ResourceSpec
    {
        if ($this->spec === null) {
            $class = AdminNav::spec($this->resource);

            // An unregistered slug has no route to reach it through, so this
            // only fires for a hand-edited snapshot.
            abort_if($class === null, 404);

            $this->spec = new $class;
        }

        return $this->spec;
    }

    /**
     * requireRole() from lib/adminAuth.ts. A null list means "any of the five
     * staff roles", which is what requireStaff() resolved to and what every
     * generic CRUD route used. The auth middleware has already proved the
     * visitor is signed in, so only the role is left to check.
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
     * @return Builder<Model>
     */
    private function newQuery(): Builder
    {
        $model = $this->spec()->model();

        return $model::query()
            ->with($this->spec()->with())
            ->orderBy($this->spec()->orderBy(), $this->spec()->orderDirection());
    }

    /**
     * The useState initialiser in ResourceForm for a new row: a locale field
     * starts as three empty strings, a checkbox falls back to true unless the
     * spec says otherwise, and everything else to "".
     *
     * @return array<string, mixed>
     */
    private function blankForm(): array
    {
        $values = [];

        foreach ($this->fields() as $field) {
            $values[$field['name']] = match (true) {
                $this->isLocale($field) => LocaleText::parse(null),
                $field['type'] === 'checkbox' => (bool) ($field['default'] ?? true),
                $field['type'] === 'number' => (string) ($field['default'] ?? 0),
                default => (string) ($field['default'] ?? ''),
            };
        }

        return $values;
    }

    /**
     * @return array<string, mixed>
     */
    private function formFrom(Model $item): array
    {
        $values = [];

        foreach ($this->fields() as $field) {
            $raw = $item->getAttribute($field['name']);

            $values[$field['name']] = match (true) {
                $this->isLocale($field) => LocaleText::parse(is_string($raw) ? $raw : null),
                $field['type'] === 'checkbox' => (bool) $raw,
                /*
                 * <input type="date"> only accepts YYYY-MM-DD, so a Carbon from
                 * the datetime cast has to be formatted down. Casting it to a
                 * string would give "2026-01-01 00:00:00", which the input
                 * silently rejects and renders blank — the editor would see an
                 * empty field and saving would wipe the date.
                 */
                $field['type'] === 'date' => $raw instanceof \DateTimeInterface ? $raw->format('Y-m-d') : ($raw === null ? '' : (string) $raw),
                /*
                 * Everything else becomes a string. A controlled <input> holds
                 * text, so leaving an int or a null in the property means
                 * Livewire diffs "3" against 3 and re-renders the field on
                 * every keystroke.
                 */
                default => $raw === null ? '' : (string) $raw,
            };
        }

        return $values;
    }

    /**
     * Coerce the validated values into what the columns actually hold — the
     * body of ResourceForm.submit().
     *
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function payload(array $values): array
    {
        $data = [];

        foreach ($this->fields() as $field) {
            $value = $values[$field['name']] ?? null;

            $data[$field['name']] = match (true) {
                $this->isLocale($field) => LocaleText::encode(
                    (string) ($value['en'] ?? ''),
                    (string) ($value['am'] ?? ''),
                    (string) ($value['om'] ?? ''),
                ),
                // Number(values[name]) || 0
                $field['type'] === 'number' => (int) $value,
                $field['type'] === 'checkbox' => (bool) $value,
                /*
                 * Blank has to become null, not "". These are the DateTime?
                 * columns — publishedAt, eventDate — and Eloquent's datetime
                 * cast hands whatever it is given to Carbon, which throws on an
                 * empty string. The React form sent "" and Prisma rejected it,
                 * which is why an editor could never clear a published date.
                 */
                $field['type'] === 'date' => trim((string) $value) === '' ? null : trim((string) $value),
                /*
                 * An unchosen select is null, not "". The React form sent ""
                 * into categoryId, which Prisma rejected as a foreign key that
                 * does not exist, so a blog post could not be saved until its
                 * category had been picked — and the resulting 500 reached the
                 * editor as the form's generic "Failed to save" alert. Every
                 * select backed by a relation is nullable in the schema, so null
                 * is what "no choice" means there.
                 *
                 * The three that are not nullable — newspost.category,
                 * project.status and islamicmessage.type — all declare a
                 * default, and falling back to it is what keeps the "— Select —"
                 * option from writing null into a NOT NULL column. The React
                 * form could not reach that state because its selects had no
                 * empty option at all; this one does, so it has to be handled.
                 */
                $field['type'] === 'select' => trim((string) $value) === ''
                    ? ($field['default'] !== null && $field['default'] !== '' ? $field['default'] : null)
                    : trim((string) $value),
                /*
                 * Trimmed, which the React form did not do, but still "" rather
                 * than null when left blank: that is what Prisma wrote into
                 * these String? columns, so existing rows and new ones stay
                 * consistent, and every read path on the public site uses `?:`
                 * or LocaleText::get(), both of which fall through on "".
                 */
                default => is_scalar($value) ? trim((string) $value) : '',
            };
        }

        return $this->withGeneratedSlug($data, $values);
    }

    /**
     * Fill in a blank slug from the field the spec nominates.
     *
     * Runs on the raw validated values rather than on $data, because a locale
     * field has already been JSON-encoded by the time $data exists and the base
     * text would have to be decoded again to read it.
     *
     * A slug the editor actually typed is left alone: uniqueSlug() in the spec's
     * rules() has already checked it against the table, and silently rewriting
     * what someone typed would be worse than refusing it.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function withGeneratedSlug(array $data, array $values): array
    {
        $base = $this->spec()->slugBase();

        if ($base === null || ! array_key_exists('slug', $data)) {
            return $data;
        }

        if (trim((string) ($data['slug'] ?? '')) !== '') {
            return $data;
        }

        // A locale field arrives as {en, am, om}; the English one seeds the slug.
        $source = $values[$base] ?? '';
        $text = is_array($source) ? (string) ($source['en'] ?? '') : (string) $source;

        $data['slug'] = Slug::unique($text, $this->spec()->model(), $this->editingId);

        return $data;
    }

    /**
     * Derived from the field list, which is the part the React form did not
     * have. Native `required` is still on the inputs, so this is the second
     * line of defence rather than the only one.
     *
     * Length ceilings come from ColumnLimits, i.e. from the column itself.
     * They used to be hardcoded on the assumption that Prisma's bare `String`
     * always meant varchar(191); that assumption is what let the content
     * columns be silently truncated, and it stopped being true when they were
     * widened to TEXT.
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
                 * have. Enforcing the whole width per locale would let a save
                 * through that the column cannot store.
                 */
                $perLocale = max(1, intdiv($limit, 3) - 12);

                foreach (LocaleText::LOCALES as $locale) {
                    $rules[$key.'.'.$locale] = [
                        // `required={f.required && locale === "en"}` — an editor
                        // could always publish before the translations existed.
                        $field['required'] && $locale === LocaleText::DEFAULT_LOCALE ? 'required' : 'nullable',
                        'string',
                        'max:'.$perLocale,
                    ];
                }

                continue;
            }

            $rules[$key] = array_values(array_merge(
                [$field['required'] ? 'required' : 'nullable'],
                match ($field['type']) {
                    'number' => ['integer'],
                    'checkbox' => ['boolean'],
                    // Lenient on purpose: the rule's job is to stop an unparseable
                    // string reaching Carbon, not to police the input's format.
                    'date' => ['date'],
                    default => ['string', 'max:'.$limit],
                },
            ));
        }

        return array_merge($rules, $this->spec()->rules($this->editingId));
    }

    private function isLocale(array $field): bool
    {
        return $field['type'] === 'localeText' || $field['type'] === 'localeTextarea';
    }

    /**
     * logActivity() from lib/activityLog.ts. lib/crudRoute.ts wrapped each call
     * in an empty catch so an audit row could never fail an editor's save; that
     * is kept, but the failure is recorded rather than dropped on the floor.
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
