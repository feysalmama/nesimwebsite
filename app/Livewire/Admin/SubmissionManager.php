<?php

namespace App\Livewire\Admin;

use App\Admin\SubmissionSpec;
use App\Models\ActivityLog;
use App\Support\AdminNav;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Component;

/**
 * components/admin/SubmissionTable.tsx, ported.
 *
 * The four public-form queues: volunteers, registrations, contact messages and
 * donation intents. There is no create and no edit — the rows were written by a
 * form on the site — so this is a list with a status dropdown and a delete, and
 * nothing else.
 *
 * Three differences from the React version:
 *
 *  - The status dropdown PATCHed and then refetched the entire list. Here the
 *    write and the re-render are one round trip.
 *  - The PATCH accepted any string. `/api/admin/volunteers/[id]/route.ts` took
 *    `{ status }` from the request body and handed it straight to Prisma, so a
 *    hand-crafted call could write "banana" into the column and the dashboard's
 *    `where('status', 'new')` count would silently drift. setStatus() below
 *    checks the value against the spec's own statusOptions() first.
 *  - Both writes are recorded in the activity log. The React routes logged
 *    nothing, so the audit trail had a hole exactly where the queues are.
 *
 * `confirm("Delete this entry?")` became wire:confirm with the same wording.
 */
class SubmissionManager extends Component
{
    /** The AdminNav slug, e.g. "volunteers". The spec is resolved from it. */
    public string $resource = '';

    /**
     * Resolved per request rather than stored: Livewire dehydrates public
     * properties only, and a column's `render` closure could not be serialised.
     */
    private ?SubmissionSpec $spec = null;

    public function mount(string $resource): void
    {
        $this->resource = $resource;
    }

    /* ── The two writes ───────────────────────────────────────────────────── */

    public function setStatus(string $id, string $status): void
    {
        $this->assertRoleAllowed($this->spec()->writeRoles());

        // Not `in_array` on a request value: the spec's list is the only thing
        // that defines what this column may hold, and the browser can call this
        // method with anything it likes.
        if (! in_array($status, $this->spec()->statusOptions(), true)) {
            return;
        }

        $item = $this->newQuery()->findOrFail($id);

        if ((string) $item->status === $status) {
            return;
        }

        $item->update(['status' => $status]);

        $this->recordActivity('update', $id);
    }

    public function delete(string $id): void
    {
        $this->assertRoleAllowed($this->spec()->writeRoles());

        $model = $this->spec()->model();

        $model::query()->findOrFail($id)->delete();

        $this->recordActivity('delete', $id);
    }

    /* ── Shape of the module, for the view ────────────────────────────────── */

    /**
     * @return Collection<int, Model>
     */
    public function items(): Collection
    {
        return $this->newQuery()->get();
    }

    /**
     * One table cell. Same logic as ResourceManager::cell() — the two components
     * render the same column declarations — but kept separate rather than shared
     * through a trait, because this one has no form and no locale fields to
     * think about and pulling the larger class in would drag both along.
     *
     * @param  array{key: string, label: string, render?: \Closure}  $column
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

    public function canWrite(): bool
    {
        $user = auth()->user();

        if ($user === null || ! $user->isStaff()) {
            return false;
        }

        $roles = $this->spec()->writeRoles();

        return $roles === null || in_array($user->role, $roles, true);
    }

    public function render(): View
    {
        $this->assertRoleAllowed($this->spec()->readRoles());

        return view('livewire.admin.submission-manager', [
            'spec' => $this->spec(),
            'columns' => $this->spec()->columns(),
            'items' => $this->items(),
            'canWrite' => $this->canWrite(),
        ]);
    }

    /* ── Internals ────────────────────────────────────────────────────────── */

    private function spec(): SubmissionSpec
    {
        if ($this->spec === null) {
            $class = AdminNav::spec($this->resource);

            /*
             * The route constrains {resource} to registered slugs, so this only
             * fires for a hand-edited snapshot — but it also fires if a slug is
             * registered against the wrong kind of spec, which is the mistake
             * worth failing loudly on.
             */
            abort_if($class === null || ! is_subclass_of($class, SubmissionSpec::class), 404);

            $this->spec = new $class;
        }

        return $this->spec;
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
     * requireRole() from lib/adminAuth.ts. Not named authorize(): Component pulls
     * in AuthorizesRequests, which owns that name.
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
     * logActivity() from lib/activityLog.ts, with the same empty-catch trade-off
     * lib/crudRoute.ts made: an audit row must never fail an editor's save, but
     * it must not disappear without a trace either.
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
