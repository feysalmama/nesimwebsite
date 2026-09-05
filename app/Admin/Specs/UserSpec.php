<?php

namespace App\Admin\Specs;

use App\Admin\AdminSpec;
use App\Models\User;

/**
 * app/admin/(protected)/users/page.tsx and its UsersClient, ported.
 *
 * The React page could never be opened. Its server component read
 * `if (session.user.role !== "ADMIN") redirect("/admin")`, and "ADMIN" is not
 * one of the five values the Role enum holds — so every SUPER_ADMIN who clicked
 * Staff Users in the sidebar was bounced back to the dashboard. The API routes
 * behind it were correct and used requireAdmin(); the page guard was the bug.
 * Here the guard is the role list below, in one place, checked by both the route
 * and the component.
 *
 * UsersClient also had no edit form, though PUT /api/admin/users/[id] existed to
 * serve one — so a wrong role or a forgotten password could only be fixed by
 * deleting the account and recreating it. The component wires that route up.
 *
 * Oldest first, matching the GET route's orderBy: the seeded administrator sits
 * at the top of the list, which is where a super admin expects to find it.
 */
final class UserSpec extends AdminSpec
{
    public function title(): string
    {
        return 'Staff Users';
    }

    public function model(): string
    {
        return User::class;
    }

    public function component(): string
    {
        return 'admin.user-manager';
    }

    public function orderDirection(): string
    {
        return 'asc';
    }

    /**
     * requireAdmin() from lib/adminAuth.ts, which all four user routes used.
     *
     * @return array<int, string>
     */
    public function readRoles(): ?array
    {
        return [User::ROLE_SUPER_ADMIN];
    }

    /**
     * @return array<int, string>
     */
    public function writeRoles(): ?array
    {
        return [User::ROLE_SUPER_ADMIN];
    }
}
