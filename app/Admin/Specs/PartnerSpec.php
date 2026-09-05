<?php

namespace App\Admin\Specs;

use App\Admin\ResourceSpec;
use App\Models\Partner;

/**
 * app/admin/(protected)/partners/page.tsx, ported.
 *
 * The logo strip on every page, in `order`, filtered on `active`.
 *
 * The websiteUrl column renders as the word "Link" rather than as the address,
 * exactly as the React table did — the cell is a presence indicator, and the URL
 * itself is often long enough to push the row's other columns off screen.
 */
final class PartnerSpec extends ResourceSpec
{
    public function title(): string
    {
        return 'Partners';
    }

    public function model(): string
    {
        return Partner::class;
    }

    public function fields(): array
    {
        return [
            ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
            ['name' => 'logoUrl', 'label' => 'Logo', 'type' => 'image'],
            ['name' => 'websiteUrl', 'label' => 'Website URL', 'type' => 'text'],
            ['name' => 'description', 'label' => 'Description', 'type' => 'textarea'],
            ['name' => 'order', 'label' => 'Order', 'type' => 'number', 'default' => 0],
            ['name' => 'active', 'label' => 'Active', 'type' => 'checkbox', 'default' => true],
        ];
    }

    public function columns(): array
    {
        return [
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'websiteUrl', 'label' => 'Website', 'render' => static fn (Partner $item) => $item->websiteUrl ? 'Link' : '—'],
            ['key' => 'active', 'label' => 'Active', 'render' => static fn (Partner $item) => $item->active ? 'Yes' : 'No'],
            ['key' => 'order', 'label' => 'Order'],
        ];
    }
}
