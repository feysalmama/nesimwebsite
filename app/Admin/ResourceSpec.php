<?php

namespace App\Admin;

use Illuminate\Validation\Rule;

/**
 * The per-module half of components/admin/ResourceManager.tsx.
 *
 * In the Next.js admin every module was a ~25-line page that handed a resource
 * key, a title, a field list and a column list to one shared client component.
 * A spec is that same declaration: everything App\Livewire\Admin\ResourceManager
 * needs in order to list, create, edit and delete rows in one table, and nothing
 * else.
 *
 * Porting a module is therefore "write a spec, register it in AdminNav::SPECS".
 * No controller, no route, no view and no Livewire component per table — which
 * matters because there are thirty-odd of them.
 *
 * Field types, mirroring the React FieldConfig union:
 *
 *   text, textarea, number, select, checkbox, image, video,
 *   localeText, localeTextarea
 *
 * plus one the React union did not have:
 *
 *   date — rendered as <input type="date">. blog-posts, resources and galleries
 *   all declared their DateTime? columns as `type: "text"`, so an editor had to
 *   type a string Prisma could parse and leaving it blank sent "" to a DateTime
 *   column, which Prisma rejected and surfaced as the form's generic
 *   "Failed to save" alert. A real date input writes a proper value or null.
 *
 * The two locale types edit the three-language JSON columns described in
 * App\Support\LocaleText. `select` took an `optionsUrl` in React and fetched its
 * choices from the API; here `options` is a plain array or a closure returning
 * one, because there is no second request to make.
 */
abstract class ResourceSpec extends AdminSpec
{
    /**
     * The editable columns, in the order the form should show them.
     *
     * Each entry needs `name`, `label` and `type`; `required`, `default` and
     * `options` are optional and filled in by the Livewire component so the
     * view never has to repeat `?? false` for every field type.
     *
     * @return array<int, array{name: string, label: string, type: string, required?: bool, default?: mixed, options?: array<int|string, mixed>|\Closure}>
     */
    abstract public function fields(): array;

    /**
     * The table columns. `render` is an optional closure taking the model and
     * returning the cell text, replacing React's `render: (item) => ...`.
     *
     * @return array<int, array{key: string, label: string, render?: \Closure}>
     */
    abstract public function columns(): array;

    /**
     * Extra validation rules merged over the ones derived from fields(). Keyed
     * by the Livewire property path, so `form.slug`, not `slug`.
     *
     * The React modules validated nothing on the server at all: the form leaned
     * on the browser's `required` attribute and the API passed raw JSON
     * straight to Prisma. Deriving rules from the field list is a deliberate
     * improvement, and this hook is where a module adds the ones that cannot be
     * derived — a unique slug, a date pair, a foreign key that must exist.
     *
     * $editingId is the row being saved, or null while creating. A spec is
     * stateless and constructed with no arguments, so the component passes it
     * in rather than the spec trying to find it — and a uniqueness rule needs
     * it to avoid rejecting a row for colliding with itself.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(?string $editingId = null): array
    {
        return [];
    }

    /**
     * For the `@unique` slug columns. Nine tables have one: service, blogpost,
     * newspost and project, plus blogcategory, tag, newscategory,
     * projectcategory and resourcecategory. Prisma enforced it and answered a
     * duplicate with a P2002 that lib/crudRoute.ts turned into a bare 500, so an
     * editor saw "Failed to save" with no idea why. Checking it here puts the
     * message on the field instead.
     *
     * Ignoring $editingId is what lets an editor reopen a row and change only
     * its title: without it the save would be rejected for colliding with the
     * very row being updated.
     *
     * Required-ness is read back off the spec's own field list rather than
     * passed in. rules() is merged over the derived rules, so a hardcoded
     * 'nullable' here would have silently dropped the 'required' that
     * blog-posts and the five category tables declare — and their slug columns
     * are NOT NULL, so the failure would have surfaced as a database error
     * instead of a message on the field.
     *
     * Only for the tables where the editor types the slug. The ones that
     * generate it declare slugBase() instead and need no rule, because
     * App\Support\Slug::unique() cannot produce a collision.
     *
     * @return array<string, array<int, mixed>>
     */
    final protected function uniqueSlug(?string $editingId = null): array
    {
        $model = $this->model();
        $required = false;

        foreach ($this->fields() as $field) {
            if ($field['name'] === 'slug') {
                $required = (bool) ($field['required'] ?? false);
            }
        }

        return [
            'form.slug' => [
                $required ? 'required' : 'nullable',
                'string',
                'max:191',
                Rule::unique((new $model)->getTable(), 'slug')->ignore($editingId, 'id'),
            ],
        ];
    }

    /**
     * The field to derive a slug from when the editor leaves it blank, or null
     * to let whatever was typed through untouched.
     *
     * This is what makes the services form's "Slug (auto-generated)" label true
     * — lib/slug.ts existed in the Next.js app but nothing ever imported it —
     * and it is why projects and news can declare a slug field at all. Their
     * React forms had none, so every row they created stored NULL and
     * getProjectBySlug()/getNewsPostBySlug() could never find it, leaving the
     * detail page unreachable.
     *
     * For a locale field the English value is the base, since a slug has to be
     * one string and the public routes are not per-language.
     */
    public function slugBase(): ?string
    {
        return null;
    }
}
