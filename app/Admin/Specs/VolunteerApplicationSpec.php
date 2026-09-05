<?php

namespace App\Admin\Specs;

use App\Admin\SubmissionSpec;
use App\Models\VolunteerApplication;

/**
 * app/admin/(protected)/volunteers/page.tsx, ported.
 *
 * Rows here are written by components/forms/VolunteerForm.tsx on the public
 * site, so there is no create and no edit — only the status dropdown and a
 * delete, which is what SubmissionSpec describes.
 *
 * Left at parity: `message` is a column the React table never showed, so the
 * one thing a volunteer actually writes is invisible in the queue. It is in the
 * database and worth a column if this module gets used.
 */
final class VolunteerApplicationSpec extends SubmissionSpec
{
    public function title(): string
    {
        return 'Volunteer Applications';
    }

    public function model(): string
    {
        return VolunteerApplication::class;
    }

    public function columns(): array
    {
        return [
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'email', 'label' => 'Email'],
            ['key' => 'phone', 'label' => 'Phone'],
            ['key' => 'city', 'label' => 'City'],
            ['key' => 'skills', 'label' => 'Skills'],
            ['key' => 'createdAt', 'label' => 'Submitted', 'render' => static fn (VolunteerApplication $item) => $item->createdAt?->format('j M Y') ?? '—'],
        ];
    }

    public function statusOptions(): array
    {
        return ['new', 'contacted', 'accepted', 'declined'];
    }
}
