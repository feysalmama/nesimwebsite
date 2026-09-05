<?php

namespace App\Admin;

/**
 * What every CMS module has in common, whatever shape its screen takes.
 *
 * The Next.js admin had three kinds of page and this hierarchy mirrors them:
 *
 *   ResourceSpec    a table you can create, edit and delete rows in
 *                   (components/admin/ResourceManager.tsx) — 21 modules
 *   SubmissionSpec  a read-only queue of public form submissions whose only
 *                   editable value is a status dropdown
 *                   (components/admin/SubmissionTable.tsx) — 4 modules
 *   SingletonSpec   one row edited in place through a sectioned form
 *                   (about-content, landing-content, president-message,
 *                   settings) — 4 modules
 *
 * Media Library, Activity Logs and Staff Users do not fit any of the three and
 * have their own components; they still extend this class so AdminNav can treat
 * every module the same way and so the sidebar, the dashboard cards and the
 * router all keep reading from one list.
 *
 * Registering a subclass in AdminNav::SPECS is the whole of adding a module:
 * that one entry creates the route, lights the sidebar link and lights the
 * dashboard card.
 */
abstract class AdminSpec
{
    /** Heading above the content, e.g. "Hero Slides". */
    abstract public function title(): string;

    /**
     * The Eloquent model this module reads and writes.
     *
     * @return class-string<\Illuminate\Database\Eloquent\Model>
     */
    abstract public function model(): string;

    /**
     * The Livewire component that renders the module. Admin\ResourceController
     * passes this to the view, which hands it to @livewire — so a module can
     * change its whole screen without a route or a controller of its own.
     */
    public function component(): string
    {
        return 'admin.resource-manager';
    }

    /**
     * Entity name written to the activity log, as `entityName` was in
     * makeCollectionRoutes(). The React modules all passed the Prisma model
     * name, which is what class_basename() gives back here.
     */
    public function entity(): string
    {
        return class_basename($this->model());
    }

    /**
     * Sort order for the list. `createdAt desc` was the default in
     * makeCollectionRoutes(); a spec overrides both methods when the module
     * passed its own orderBy, as hero-slides did with `{ order: "asc" }`.
     *
     * Eight tables have no createdAt column at all — the six category/tag
     * tables, faqitem and impactstat — and their specs must override this,
     * because there is no column to fall back on and MySQL would reject the
     * query outright.
     */
    public function orderBy(): string
    {
        return 'createdAt';
    }

    public function orderDirection(): string
    {
        return 'desc';
    }

    /**
     * Relations the list or the form needs eager-loaded, so a column that shows
     * `$item->category->name` does not issue one query per row.
     *
     * @return array<int, string>
     */
    public function with(): array
    {
        return [];
    }

    /**
     * Roles allowed to open the module at all. null means any of the five staff
     * roles, which is what requireStaff() resolved to and what every route
     * built by makeCollectionRoutes() used.
     *
     * @return array<int, string>|null
     */
    public function readRoles(): ?array
    {
        return null;
    }

    /**
     * Roles allowed to create, update and delete.
     *
     * lib/adminAuth.ts split read from write in three places: the four singleton
     * tables (about-content, landing-content, president-message, settings) and
     * activity-logs were readable by any staff member but writable only by
     * SUPER_ADMIN and CONTENT_ADMIN, and users/ was SUPER_ADMIN throughout.
     * Everywhere else the two matched, which is what this default reproduces.
     *
     * @return array<int, string>|null
     */
    public function writeRoles(): ?array
    {
        return $this->readRoles();
    }

    /**
     * The read/write split lib/adminAuth.ts applied to the singletons and to
     * activity-logs. Named rather than repeated inline in each of those specs so
     * the two lists cannot drift apart.
     *
     * @return array<int, string>
     */
    final protected function contentEditors(): array
    {
        return ['SUPER_ADMIN', 'CONTENT_ADMIN'];
    }
}
