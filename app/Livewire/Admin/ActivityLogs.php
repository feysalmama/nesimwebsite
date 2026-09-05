<?php

namespace App\Livewire\Admin;

use App\Admin\AdminSpec;
use App\Models\ActivityLog;
use App\Support\AdminNav;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * app/admin/(protected)/activity-logs/page.tsx, ported.
 *
 * Read-only by design: ActivityLogSpec::writeRoles() returns an empty list, so
 * there is no method here that writes and no role that could call one. An audit
 * trail a SUPER_ADMIN can prune is not an audit trail.
 *
 * What changed and why:
 *
 *  - The React page built its entity dropdown from `items` — the hundred rows it
 *    had just fetched. Filtering by "BlogPost" therefore left BlogPost as the
 *    only option in the list, and the editor could not get back to another entity
 *    without reloading the page. entities() and actions() below query the whole
 *    table, so the options never depend on the filter already applied.
 *  - The action filter the API route supported (`?action=`) had no control in the
 *    page. It has one now.
 *  - `limit=100` with no page parameter became pagination, for the reason it did
 *    in the media library: the log only ever grows.
 *  - entityId and details are shown. The React table dropped both, so a delete
 *    entry said "Program" and nothing about which program — the one question the
 *    log exists to answer.
 */
class ActivityLogs extends Component
{
    use WithPagination;

    /** The AdminNav slug, always "activity-logs". The spec is resolved from it. */
    public string $resource = '';

    /** An entity name from the dropdown, or "" for all. */
    public string $entity = '';

    /** An action from the dropdown, or "" for all. */
    public string $action = '';

    private ?AdminSpec $spec = null;

    private const PER_PAGE = 50;

    public function mount(string $resource): void
    {
        $this->resource = $resource;
    }

    /* ── Filters ──────────────────────────────────────────────────────────── */

    /**
     * Both dropdowns bind straight to the properties with wire:model.live, so
     * these hooks are the only thing standing between the browser and the query.
     * Every public property on a Livewire component can be set from a
     * hand-crafted request to any string the caller likes; an arbitrary one is
     * reset to "all" here, and either change moves back to page one, which a
     * filter that kept the old page number would leave looking empty.
     */
    public function updatedEntity(): void
    {
        if ($this->entity !== '' && ! in_array($this->entity, $this->entities()->all(), true)) {
            $this->entity = '';
        }

        $this->resetPage();
    }

    public function updatedAction(): void
    {
        if ($this->action !== '' && ! in_array($this->action, $this->actions()->all(), true)) {
            $this->action = '';
        }

        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->entity = '';
        $this->action = '';
        $this->resetPage();
    }

    /* ── Shape of the module, for the view ────────────────────────────────── */

    public function items(): LengthAwarePaginator
    {
        return $this->newQuery()->paginate(self::PER_PAGE);
    }

    /**
     * Every entity name in the log, for the dropdown. Distinct over the whole
     * table rather than over the visible rows — see the class docblock.
     *
     * @return Collection<int, string>
     */
    public function entities(): Collection
    {
        return $this->distinctValues('entity');
    }

    /**
     * Every action in the log. Not a hardcoded list, because the values come
     * from whatever wrote them: lib/activityLog.ts used create/update/delete, the
     * Next.js auth routes added login and logout, and a module is free to record
     * anything else. A list written here would silently hide the ones it forgot.
     *
     * @return Collection<int, string>
     */
    public function actions(): Collection
    {
        return $this->distinctValues('action');
    }

    /**
     * The badge colours actionBadge() used in the React page, extended with the
     * two the auth routes wrote and a neutral fallback for anything else.
     */
    public function badge(string $action): string
    {
        return match ($action) {
            'create' => 'bg-leaf/15 text-forest',
            'update' => 'bg-globe/15 text-globe',
            'delete' => 'bg-danger/15 text-danger',
            'login' => 'bg-sun/20 text-ink',
            'logout' => 'bg-stone/15 text-ink/70',
            default => 'bg-stone/15 text-stone',
        };
    }

    /** "Program" reads better than "program" in a table of them. */
    public function label(string $value): string
    {
        return str($value)->headline()->toString();
    }

    public function render(): View
    {
        /*
         * In render() and not only in mount(): render() runs on the page load and
         * on every subsequent update, so a replayed snapshot cannot read a module
         * the signed-in role was never allowed to open.
         */
        $this->assertRoleAllowed($this->spec()->readRoles());

        return view('livewire.admin.activity-logs', [
            'spec' => $this->spec(),
            'items' => $this->items(),
            'entities' => $this->entities(),
            'actions' => $this->actions(),
        ]);
    }

    /* ── Internals ────────────────────────────────────────────────────────── */

    private function spec(): AdminSpec
    {
        if ($this->spec === null) {
            $class = AdminNav::spec($this->resource);

            abort_if($class === null, 404);

            $this->spec = new $class;
        }

        return $this->spec;
    }

    /**
     * @return Builder<ActivityLog>
     */
    private function newQuery(): Builder
    {
        return ActivityLog::query()
            ->with('user:id,name,email')
            ->when($this->entity !== '', fn (Builder $query) => $query->where('entity', $this->entity))
            ->when($this->action !== '', fn (Builder $query) => $query->where('action', $this->action))
            ->orderBy('createdAt', 'desc');
    }

    /**
     * @return Collection<int, string>
     */
    private function distinctValues(string $column): Collection
    {
        return ActivityLog::query()
            ->distinct()
            ->whereNotNull($column)
            ->orderBy($column)
            ->pluck($column)
            ->filter(static fn ($value) => is_string($value) && $value !== '')
            ->values();
    }

    /**
     * requireRole("SUPER_ADMIN", "CONTENT_ADMIN") from lib/adminAuth.ts. Not
     * named authorize(): Component pulls in AuthorizesRequests, which owns that
     * name for Gate checks.
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
}
