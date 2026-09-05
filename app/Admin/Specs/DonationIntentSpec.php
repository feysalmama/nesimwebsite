<?php

namespace App\Admin\Specs;

use App\Admin\SubmissionSpec;
use App\Models\DonationIntent;

/**
 * app/admin/(protected)/donations/page.tsx, ported.
 *
 * An "intent", not a payment: components/forms/DonateForm.tsx records what the
 * visitor meant to give and nothing is charged, which is why the statuses are
 * confirmed by hand. The dashboard counts `pending` as outstanding work.
 *
 * `amount` is an Int column and the React label hardcodes the currency, so the
 * column heading says ETB. DonationIntent.currency defaults to "ETB" and nothing
 * ever writes anything else.
 */
final class DonationIntentSpec extends SubmissionSpec
{
    public function title(): string
    {
        return 'Donation Intents';
    }

    public function model(): string
    {
        return DonationIntent::class;
    }

    public function columns(): array
    {
        return [
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'email', 'label' => 'Email'],
            ['key' => 'amount', 'label' => 'Amount (ETB)'],
            ['key' => 'method', 'label' => 'Method'],
            ['key' => 'createdAt', 'label' => 'Submitted', 'render' => static fn (DonationIntent $item) => $item->createdAt?->format('j M Y') ?? '—'],
        ];
    }

    public function statusOptions(): array
    {
        return ['pending', 'confirmed', 'failed'];
    }
}
