<?php

namespace App\Admin;

/**
 * The four singleton screens: about-content, landing-content, president-message
 * and settings.
 *
 * Each of those React pages was the same shape again — fetch the one row into
 * useState, render a stack of labelled sections, PUT the whole object back on
 * Save — but none of them used ResourceManager, because there is no list, no
 * create and no delete. A ResourceSpec forced to describe them would have to
 * fake all three.
 *
 * They differ enough from each other that the sections are declared rather than
 * hardcoded in the view: settings is eight flat sections, about-content is four
 * with a raw JSON textarea, president-message is one, and landing-content is six
 * tabs carrying repeaters over JSON array columns.
 *
 * Field types on top of the ResourceSpec set:
 *
 *   color     a swatch picker paired with a hex text input, as settings used for
 *             primaryColor and accentColor
 *   json      a monospace textarea holding the column's raw JSON. about-content
 *             exposed timelineData this way, and unlike the React version this
 *             one is validated as JSON before it is written — an editor who
 *             dropped a bracket used to save it and break the public page.
 *   repeater  a list of sub-records stored as one JSON array column, with an
 *             add button, a remove button per row and a maximum. This is what
 *             landing-content's impactStats, factsItems, reachRegions,
 *             processSteps and storiesItems all were.
 *   imageList the same, over a JSON array of bare strings rather than objects —
 *             landing-content's communityImages, which the home page reads as a
 *             flat list of URLs. Kept apart from `repeater` because the stored
 *             shape differs and HomeController would render nothing if it moved.
 *
 * Section keys, all optional:
 *
 *   tab          the tab strip label, when tabbed() is true. landing-content's
 *                tabs said "Community" above a card headed "Giving Back to Our
 *                Communities", so the two are declared separately.
 *   description  the one-line note under the section heading.
 *   layout       'stack' (default) puts every field on its own row; 'grid' uses
 *                the two-column grid settings and about-content's hero section
 *                had, in which a text field takes half the width unless it says
 *                otherwise; 'mixed' is the same grid with the default inverted —
 *                every field spans both columns unless it declares
 *                `span => 'half'`, which is what landing-content's Impact and
 *                Facts tabs needed for their two side-by-side CTA inputs.
 *
 * Field keys beyond name/label/type, all optional: required, rows, placeholder,
 * span ('full'|'half'), hint, group — the title of a bordered box that gathers
 * consecutive fields, as the impact tab's "Quote Overlay" did — and, for the two
 * collection types, max, addLabel, rowLabel, inline and subfields.
 */
abstract class SingletonSpec extends AdminSpec
{
    public function component(): string
    {
        return 'admin.singleton-editor';
    }

    /** The one row this table holds. */
    abstract public function rowId(): string;

    /**
     * The sections, in display order.
     *
     * @return array<int, array{title: string, description?: string, fields: array<int, array<string, mixed>>}>
     */
    abstract public function sections(): array;

    /** Line under the heading, as PageHeader's `subtitle` prop. */
    public function subtitle(): string
    {
        return '';
    }

    /**
     * Render the sections as a tab strip rather than one long stack. Only
     * landing-content did, and only because six sections of repeaters do not fit
     * on a screen; the other three read better scrolled.
     */
    public function tabbed(): bool
    {
        return false;
    }

    /**
     * Readable by any of the five staff roles, writable by the two content
     * roles — the split lib/adminAuth.ts applied to all four singleton routes,
     * where GET went through requireStaff() and PUT through
     * requireRole(["SUPER_ADMIN", "CONTENT_ADMIN"]).
     */
    public function writeRoles(): ?array
    {
        return $this->contentEditors();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Values to seed the editor with when the row does not exist yet.
     *
     * Only settings needs this. Its React page read through
     * lib/content.ts::getSettings(), which falls back to hardcoded defaults when
     * the row is missing, and those defaults live in PHP — Prisma applies
     * @default client-side, so MySQL has none. Seeding the editor keeps a first
     * Save on an unseeded database from writing NULL over them.
     *
     * @return array<string, mixed>
     */
    public function fallbacks(): array
    {
        return [];
    }

    /**
     * Every field declared by every section, flattened. The editor uses it to
     * build the property bag and to validate, so a field no section renders
     * cannot be written — which matters because these models, like all the
     * others here, leave $guarded empty.
     *
     * @return array<int, array<string, mixed>>
     */
    final public function allFields(): array
    {
        $fields = [];

        foreach ($this->sections() as $section) {
            foreach ($section['fields'] ?? [] as $field) {
                $fields[] = $field;
            }
        }

        return $fields;
    }
}
