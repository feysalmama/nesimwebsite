<?php

namespace App\Admin\Specs;

use App\Admin\ResourceSpec;
use App\Models\MembershipCategory;

/**
 * app/admin/(protected)/membership-categories/page.tsx, ported.
 *
 * Restricted to the three roles the Membership sidebar section was gated on in
 * components/admin/AdminSidebar.tsx. The React API routes behind it went through
 * requireStaff() like everything else, so the group was hidden from the
 * navigation but still reachable by URL — an EDITOR who typed the address got in.
 * Declaring it here closes that rather than reproducing it.
 *
 * No createdAt column, so `order` decides the listing, as it does for the other
 * tables Prisma declared without timestamps.
 *
 * All four text columns hold three languages — the seed writes each through
 * loc(en, am, om) and the membership pages resolve them through LocaleText —
 * while the React page declared all four as plain text. A membership tier is
 * the one thing on the site nobody wants half-translated, so they are locale
 * fields here.
 */
final class MembershipCategorySpec extends ResourceSpec
{
    public function title(): string
    {
        return 'Membership Categories';
    }

    public function model(): string
    {
        return MembershipCategory::class;
    }

    public function fields(): array
    {
        return [
            ['name' => 'name', 'label' => 'Name', 'type' => 'localeText', 'required' => true],
            ['name' => 'description', 'label' => 'Description', 'type' => 'localeTextarea'],
            ['name' => 'requirements', 'label' => 'Requirements', 'type' => 'localeTextarea'],
            ['name' => 'benefits', 'label' => 'Benefits', 'type' => 'localeTextarea'],
            ['name' => 'published', 'label' => 'Published', 'type' => 'checkbox', 'default' => true],
            ['name' => 'order', 'label' => 'Order', 'type' => 'number', 'default' => 0],
        ];
    }

    public function columns(): array
    {
        return [
            ['key' => 'name', 'label' => 'Name', 'render' => static fn (MembershipCategory $item) => $item->text('name', 'en')],
            ['key' => 'published', 'label' => 'Published', 'render' => static fn (MembershipCategory $item) => $item->published ? 'Yes' : 'No'],
            ['key' => 'order', 'label' => 'Order'],
        ];
    }

    public function orderBy(): string
    {
        return 'order';
    }

    public function orderDirection(): string
    {
        return 'asc';
    }

    public function readRoles(): ?array
    {
        return ['SUPER_ADMIN', 'MEMBERSHIP_ADMIN', 'CONTENT_ADMIN'];
    }
}
