<?php

namespace App\Admin\Specs;

use App\Admin\AdminSpec;
use App\Models\ActivityLog;

/**
 * app/admin/(protected)/activity-logs/page.tsx, ported.
 *
 * The audit trail every other module writes to and nothing else reads. It is
 * the one module with no write path at all, which is the point: an editor who
 * could delete a log row could erase the evidence of the row they deleted.
 * writeRoles() therefore returns an empty list rather than null, so every
 * mutating method on the component aborts for every role including SUPER_ADMIN.
 *
 * lib/adminAuth.ts gated the route with requireRole("SUPER_ADMIN",
 * "CONTENT_ADMIN") — narrower than the requireStaff() most modules used — and
 * readRoles() reproduces that.
 */
final class ActivityLogSpec extends AdminSpec
{
    public function title(): string
    {
        return 'Activity Logs';
    }

    public function model(): string
    {
        return ActivityLog::class;
    }

    public function component(): string
    {
        return 'admin.activity-logs';
    }

    public function readRoles(): ?array
    {
        return $this->contentEditors();
    }

    /**
     * Empty, not null. null means "same as readRoles()", which here would allow
     * the two content roles to write to the log through the component.
     *
     * @return array<int, string>
     */
    public function writeRoles(): ?array
    {
        return [];
    }
}
