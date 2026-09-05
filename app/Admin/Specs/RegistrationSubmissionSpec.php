<?php

namespace App\Admin\Specs;

use App\Admin\SubmissionSpec;
use App\Models\RegistrationSubmission;

/**
 * app/admin/(protected)/registrations/page.tsx, ported.
 *
 * Written by components/forms/RegisterForm.tsx. `program` holds whatever the
 * visitor typed into the free-text field, not a foreign key, which is why it
 * needs no eager loading.
 *
 * Left at parity: `notes` is a column the React table never showed.
 */
final class RegistrationSubmissionSpec extends SubmissionSpec
{
    public function title(): string
    {
        return 'Registrations';
    }

    public function model(): string
    {
        return RegistrationSubmission::class;
    }

    public function columns(): array
    {
        return [
            ['key' => 'fullName', 'label' => 'Name'],
            ['key' => 'email', 'label' => 'Email'],
            ['key' => 'phone', 'label' => 'Phone'],
            ['key' => 'program', 'label' => 'Program'],
            ['key' => 'city', 'label' => 'City'],
            ['key' => 'createdAt', 'label' => 'Submitted', 'render' => static fn (RegistrationSubmission $item) => $item->createdAt?->format('j M Y') ?? '—'],
        ];
    }

    public function statusOptions(): array
    {
        return ['new', 'confirmed', 'cancelled'];
    }
}
