<?php

namespace App\Admin\Specs;

use App\Admin\ResourceSpec;
use App\Models\TeamMember;

/**
 * app/admin/(protected)/team/page.tsx, ported.
 *
 * Left at parity: `isLeader` is a column the React form never exposed, and it is
 * what separates the leadership page from the rest of the team. Adding it is a
 * one-line change here if that page needs managing from the CMS.
 */
final class TeamMemberSpec extends ResourceSpec
{
    public function title(): string
    {
        return 'Team';
    }

    public function model(): string
    {
        return TeamMember::class;
    }

    public function fields(): array
    {
        return [
            ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
            ['name' => 'role', 'label' => 'Role', 'type' => 'localeText', 'required' => true],
            ['name' => 'bio', 'label' => 'Short bio', 'type' => 'localeTextarea'],
            ['name' => 'photoUrl', 'label' => 'Photo', 'type' => 'image'],
            ['name' => 'order', 'label' => 'Order', 'type' => 'number', 'default' => 0],
            ['name' => 'published', 'label' => 'Published', 'type' => 'checkbox', 'default' => true],
        ];
    }

    public function columns(): array
    {
        return [
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'role', 'label' => 'Role', 'render' => static fn (TeamMember $item) => $item->text('role', 'en')],
            ['key' => 'published', 'label' => 'Published', 'render' => static fn (TeamMember $item) => $item->published ? 'Yes' : 'No'],
        ];
    }
}
