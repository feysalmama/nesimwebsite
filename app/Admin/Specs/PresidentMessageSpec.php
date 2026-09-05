<?php

namespace App\Admin\Specs;

use App\Admin\SingletonSpec;
use App\Models\PresidentMessage;

/**
 * app/admin/(protected)/president-message/page.tsx, ported.
 *
 * One row, five fields, a Save button. The React page fetched
 * /api/admin/president-message into useState and PUT the whole object back; here
 * the row is loaded in mount() and written in save().
 *
 * Left at parity: `name`, `position` and `message` are required by the schema
 * (they are the only non-nullable columns on the table besides id), and the
 * React page marked none of them as such — it relied on the PUT failing. The
 * three required flags below are what the database already enforced, surfaced
 * before the write instead of after it.
 *
 * `position` and `message` are locale fields: the row holds them as
 * {"en":…,"am":…,"om":…}, and home.blade.php reads them back through
 * `$president->text('message')` and `$president->text('position')`. The React
 * page bound one <input> over each, so the chairman's title arrived in the box
 * as raw JSON and the first keystroke replaced all three languages with a plain
 * string. `name` stays single-language — every row holds a person's name as
 * plain text, and a name is the same in all three.
 */
final class PresidentMessageSpec extends SingletonSpec
{
    public function title(): string
    {
        return "Chairman's Message";
    }

    public function subtitle(): string
    {
        return 'Edit the featured message from the organization chairman';
    }

    public function model(): string
    {
        return PresidentMessage::class;
    }

    public function rowId(): string
    {
        return PresidentMessage::SINGLETON_ID;
    }

    public function sections(): array
    {
        return [
            [
                'title' => "Chairman's Message",
                'fields' => [
                    ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
                    ['name' => 'position', 'label' => 'Position', 'type' => 'localeText', 'required' => true],
                    ['name' => 'photoUrl', 'label' => 'Photo', 'type' => 'image'],
                    // The React page gave this one rows={8}; it is the longest
                    // thing on the screen and deserves the height.
                    ['name' => 'message', 'label' => 'Message', 'type' => 'localeTextarea', 'required' => true, 'rows' => 8],
                    ['name' => 'signatureUrl', 'label' => 'Signature', 'type' => 'image'],
                ],
            ],
        ];
    }
}
