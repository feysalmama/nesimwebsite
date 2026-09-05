<?php

namespace App\Admin;

/**
 * The per-module half of components/admin/SubmissionTable.tsx.
 *
 * Four modules used that component instead of ResourceManager: the volunteer,
 * registration, contact and donation queues. They hold rows the public site
 * created, so there is nothing to add and nothing to edit — an editor reads the
 * submission, moves its status along, and deletes it once it has been dealt
 * with. That is a different enough screen that forcing it through a
 * ResourceSpec would mean a form nobody can open.
 *
 * The React component PATCHed { status } and then refetched the whole list;
 * here App\Livewire\Admin\SubmissionManager writes and re-renders in one round
 * trip.
 */
abstract class SubmissionSpec extends AdminSpec
{
    public function component(): string
    {
        return 'admin.submission-manager';
    }

    /**
     * The table columns. Same shape as ResourceSpec::columns(), including the
     * optional `render` closure — every one of the four React pages used it to
     * format createdAt as a local date.
     *
     * @return array<int, array{key: string, label: string, render?: \Closure}>
     */
    abstract public function columns(): array;

    /**
     * The values the status dropdown offers, in the order the React page listed
     * them. They are written straight into the `status` column, which Prisma
     * declared as a plain String with a default rather than an enum, so nothing
     * else constrains them.
     *
     * @return array<int, string>
     */
    abstract public function statusOptions(): array;

    /**
     * Newest first, which is what `/api/admin/{resource}` returned through
     * makeCollectionRoutes() and what a queue wants: the submission nobody has
     * dealt with yet is at the top.
     */
    public function orderDirection(): string
    {
        return 'desc';
    }
}
