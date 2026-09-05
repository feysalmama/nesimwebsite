<?php

namespace App\Admin\Specs;

use App\Admin\ResourceSpec;
use App\Models\ImpactStat;

/**
 * app/admin/(protected)/impact/page.tsx, ported.
 *
 * Same ordering story as FAQ: no createdAt column, so `order` decides. The
 * counters on the home page animate in this sequence.
 *
 * Left at parity: prefix, description and active are columns the React form
 * never exposed, and active is what ImpactStat::scopeActive() filters the public
 * site on — so a row created here is visible whether or not it should be, until
 * someone sets it in the database. Worth adding if the module gets used.
 */
final class ImpactStatSpec extends ResourceSpec
{
    public function title(): string
    {
        return 'Impact Stats';
    }

    public function model(): string
    {
        return ImpactStat::class;
    }

    public function fields(): array
    {
        return [
            ['name' => 'label', 'label' => 'Label (e.g. Students Reached)', 'type' => 'localeText', 'required' => true],
            ['name' => 'value', 'label' => 'Value', 'type' => 'number', 'required' => true, 'default' => 0],
            ['name' => 'suffix', 'label' => 'Suffix (e.g. +)', 'type' => 'text'],
            ['name' => 'icon', 'label' => 'Icon', 'type' => 'text'],
            ['name' => 'order', 'label' => 'Order', 'type' => 'number', 'default' => 0],
        ];
    }

    public function columns(): array
    {
        return [
            ['key' => 'label', 'label' => 'Label', 'render' => static fn (ImpactStat $item) => $item->text('label', 'en')],
            ['key' => 'value', 'label' => 'Value'],
            ['key' => 'suffix', 'label' => 'Suffix'],
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
}
