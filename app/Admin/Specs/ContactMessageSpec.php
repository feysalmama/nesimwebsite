<?php

namespace App\Admin\Specs;

use App\Admin\SubmissionSpec;
use App\Models\ContactMessage;

/**
 * app/admin/(protected)/messages/page.tsx, ported.
 *
 * Written by components/forms/ContactForm.tsx. The status here is a read
 * receipt rather than a workflow — "new" is what the dashboard counts as unread.
 *
 * Left at parity: `phone` is a column the React table never showed, so an
 * enquiry that left a number has to be opened in the database to call back.
 */
final class ContactMessageSpec extends SubmissionSpec
{
    public function title(): string
    {
        return 'Contact Messages';
    }

    public function model(): string
    {
        return ContactMessage::class;
    }

    public function columns(): array
    {
        return [
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'email', 'label' => 'Email'],
            ['key' => 'subject', 'label' => 'Subject'],
            ['key' => 'message', 'label' => 'Message'],
            ['key' => 'createdAt', 'label' => 'Received', 'render' => static fn (ContactMessage $item) => $item->createdAt?->format('j M Y') ?? '—'],
        ];
    }

    public function statusOptions(): array
    {
        return ['new', 'read', 'replied'];
    }
}
