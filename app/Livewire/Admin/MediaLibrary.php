<?php

namespace App\Livewire\Admin;

use App\Admin\AdminSpec;
use App\Models\ActivityLog;
use App\Models\Media;
use App\Support\AdminNav;
use App\Support\ColumnLimits;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * app/admin/(protected)/media/page.tsx, ported.
 *
 * A grid of the files Admin\UploadController has written, with the filters the
 * React page had and the four things the panel needs from it: upload, rename the
 * alt text, copy the URL, delete.
 *
 * What changed and why:
 *
 *  - The React page fetched with `limit=100` and no page parameter, so the 101st
 *    file existed in the database and was invisible in the panel with nothing to
 *    tell the editor so. This paginates.
 *  - Multi-upload posted the files one at a time and then reported
 *    "3 uploaded, 2 failed" through a toast. The loop is still one file per
 *    request — UploadController's MIME and size checks are per file, and a batch
 *    endpoint would have to reinvent them — but the result now lands in a message
 *    on the page, where it stays long enough to read.
 *  - Alt text became editable. PUT /api/admin/media/[id] existed and nothing in
 *    the React app ever called it, so the only way to fix a wrong description was
 *    to re-upload the file.
 *  - Both writes are recorded in the activity log, as the React routes did not.
 *
 * Deleting removes the Media row and deliberately leaves the file on disk, which
 * is what the React DELETE did and what the confirmation still says: content
 * already pointing at /uploads/… would break if the file went with the row.
 */
class MediaLibrary extends Component
{
    use WithPagination;

    /** The AdminNav slug, always "media". The spec is resolved from it. */
    public string $resource = '';

    /** One of "", "image", "video" — the chip the React page kept in useState. */
    public string $filter = '';

    /** Free text matched against the alt text and the URL. */
    public string $search = '';

    /** The row whose alt text is open in the drawer, or null. */
    public ?string $editingId = null;

    public string $altText = '';

    /** Result of the last upload batch, or of an alt-text save. */
    public ?string $notice = null;

    public ?string $uploadError = null;

    private ?AdminSpec $spec = null;

    private const PER_PAGE = 60;

    /** The only two values fileType is ever written with, from UploadController. */
    private const TYPES = ['', 'image', 'video'];

    public function mount(string $resource): void
    {
        $this->resource = $resource;
    }

    /* ── Filters ──────────────────────────────────────────────────────────── */

    public function setType(string $filter): void
    {
        /*
         * Checked against the list rather than assigned: setType() is callable
         * from the browser with any string, and an undeclared one would render an
         * empty grid that looks like a library with nothing in it.
         */
        if (! in_array($filter, self::TYPES, true)) {
            return;
        }

        $this->filter = $filter;
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->filter = '';
        $this->search = '';
        $this->resetPage();
    }

    /* ── Alt text ─────────────────────────────────────────────────────────── */

    public function openAlt(string $id): void
    {
        $item = $this->newQuery()->findOrFail($id);

        $this->editingId = (string) $item->getKey();
        $this->altText = (string) ($item->altText ?? '');
        $this->uploadError = null;
        $this->resetErrorBag();
    }

    public function closeAlt(): void
    {
        $this->editingId = null;
        $this->altText = '';
        $this->uploadError = null;
        $this->resetErrorBag();
    }

    public function saveAlt(): void
    {
        $this->assertRoleAllowed($this->spec()->writeRoles());

        if ($this->editingId === null) {
            return;
        }

        $limit = ColumnLimits::get((new Media)->getTable(), 'altText');

        $this->validate([
            'altText' => ['nullable', 'string', 'max:'.$limit],
        ], [], ['altText' => 'alt text']);

        $item = Media::query()->findOrFail($this->editingId);

        // Empty becomes null rather than '': the column is nullable and the card
        // falls back to the filename when it is null, so a cleared description
        // should read as "none given" and not as an empty string.
        $alt = trim($this->altText);

        $item->update(['altText' => $alt === '' ? null : $alt]);

        $this->recordActivity('update', (string) $item->getKey());

        $this->notice = 'Alt text saved.';
        $this->closeAlt();
    }

    /* ── Delete ───────────────────────────────────────────────────────────── */

    public function delete(string $id): void
    {
        $this->assertRoleAllowed($this->spec()->writeRoles());

        $item = Media::query()->findOrFail($id);

        $item->delete();

        $this->recordActivity('delete', $id);

        $this->notice = 'Removed from the library. The file itself is still on the server.';

        // The drawer could be open on the row that just went.
        if ($this->editingId === $id) {
            $this->closeAlt();
        }

        /*
         * The last card on the page may have been the one deleted, which would
         * leave the editor looking at an empty grid with a page 2 still on offer.
         */
        $lastPage = max(1, (int) ceil($this->newQuery()->count() / self::PER_PAGE));

        if ($this->getPage() > $lastPage) {
            $this->setPage($lastPage);
        }
    }

    /* ── Upload result ────────────────────────────────────────────────────── */

    /**
     * Called once by the handler in the component view after it has posted every
     * chosen file to Admin\UploadController.
     *
     * All three arguments come from the browser and none of them are trusted as
     * facts about what happened — the grid is re-read from the database either
     * way, and the counts only shape the sentence.
     */
    public function uploadsFinished(int $succeeded, int $failed, string $reason): void
    {
        $this->assertRoleAllowed($this->spec()->writeRoles());

        $succeeded = max(0, $succeeded);
        $failed = max(0, $failed);
        $reason = Str::limit(trim($reason), 200);

        if ($succeeded > 0) {
            // One entry rather than one per file: the log says who added to the
            // library and when, and a hundred-file drop should not be a hundred
            // rows saying the same thing.
            $this->recordActivity('create', null);

            $this->resetPage();
        }

        $this->notice = match (true) {
            $failed === 0 && $succeeded > 0 => $succeeded.' file'.($succeeded === 1 ? '' : 's').' uploaded.',
            $succeeded === 0 => null,
            default => $succeeded.' uploaded, '.$failed.' failed.',
        };

        $this->uploadError = match (true) {
            $failed === 0 => null,
            $reason !== '' => 'Upload failed: '.$reason,
            default => 'Upload failed.',
        };
    }

    /* ── Shape of the module, for the view ────────────────────────────────── */

    public function items(): LengthAwarePaginator
    {
        /*
         * paginate() already counts the rows matching the filters, and the view
         * reads that count through $items->total() for the subtitle — so the
         * panel costs two queries, not three.
         */
        return $this->newQuery()->paginate(self::PER_PAGE);
    }

    /**
     * "1.4 MB" / "812.3 KB" / "—" — formatSize() from the React page, which
     * returned an empty string for a null size. A null means UploadController
     * could not read the temp file's size, and a blank under the filename looks
     * like a rendering bug rather than a missing value.
     */
    public function size(?int $bytes): string
    {
        if ($bytes === null || $bytes <= 0) {
            return '—';
        }

        if ($bytes < 1024) {
            return $bytes.' B';
        }

        if ($bytes < 1024 * 1024) {
            return round($bytes / 1024, 1).' KB';
        }

        return round($bytes / (1024 * 1024), 1).' MB';
    }

    /** The filename at the end of the URL, for a card with no alt text. */
    public function basename(Media $item): string
    {
        return basename((string) $item->url);
    }

    /**
     * The row the alt-text drawer is open on.
     *
     * Passed to the view rather than left for it to find, because the drawer sits
     * after the grid in the template and Blade's $item would still hold whichever
     * card the @foreach stopped on — the last one on the page, not the one being
     * edited.
     */
    public function editingItem(): ?Media
    {
        return $this->editingId === null ? null : Media::query()->find($this->editingId);
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
        /*
         * In render() and not only in mount(): render() runs on the page load and
         * on every subsequent update, so a replayed snapshot cannot read or write
         * a module the signed-in role was never allowed to open.
         */
        $this->assertRoleAllowed($this->spec()->readRoles());

        return view('livewire.admin.media-library', [
            'spec' => $this->spec(),
            'items' => $this->items(),
            'editing' => $this->editingItem(),
            'types' => [
                '' => 'All',
                'image' => 'Images',
                'video' => 'Videos',
            ],
            'canWrite' => $this->canWrite(),
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
     * @return Builder<Media>
     */
    private function newQuery(): Builder
    {
        $search = trim($this->search);

        return Media::query()
            ->when($this->filter !== '', fn (Builder $query) => $query->where('fileType', $this->filter))
            ->when($search !== '', static function (Builder $query) use ($search): void {
                // Grouped, or the OR would escape the fileType condition above and
                // the Videos chip would appear to search the whole library.
                $query->where(static function (Builder $inner) use ($search): void {
                    $like = '%'.$search.'%';

                    $inner->where('altText', 'like', $like)
                        ->orWhere('url', 'like', $like);
                });
            })
            ->orderBy('createdAt', 'desc');
    }

    /**
     * requireStaff() from lib/adminAuth.ts, which both media routes used. Not
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

    /**
     * logActivity() from lib/activityLog.ts, with the same empty-catch trade-off
     * the React routes made: an audit row must never fail an editor's save, but
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
