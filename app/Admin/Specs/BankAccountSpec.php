<?php

namespace App\Admin\Specs;

use App\Admin\ResourceSpec;
use App\Models\BankAccount;

/**
 * The bank accounts Site\DonateController lists above the donation form.
 *
 * PartnerSpec's shape — a name, a logo, an `order` and an `active` toggle —
 * with the account itself in the middle. `accountName` is the holder's name and
 * stays optional: some transfers only need the number, and a bank whose account
 * is registered under the organisation's own name would only be repeating what
 * the footer already says.
 *
 * Sorted by `order` rather than by age, for the reason hero-slides is: the
 * strip on /donate reads top to bottom, and an editor deciding which bank comes
 * first should not have to delete and re-add a row to say so.
 */
final class BankAccountSpec extends ResourceSpec
{
    public function title(): string
    {
        return 'Bank Accounts';
    }

    public function model(): string
    {
        return BankAccount::class;
    }

    public function fields(): array
    {
        return [
            ['name' => 'name', 'label' => 'Bank Name', 'type' => 'text', 'required' => true],
            ['name' => 'accountNumber', 'label' => 'Account Number', 'type' => 'text', 'required' => true],
            ['name' => 'accountName', 'label' => 'Account Holder Name', 'type' => 'text'],
            ['name' => 'logoUrl', 'label' => 'Logo', 'type' => 'image'],
            ['name' => 'order', 'label' => 'Order', 'type' => 'number', 'default' => 0],
            ['name' => 'active', 'label' => 'Active', 'type' => 'checkbox', 'default' => true],
        ];
    }

    public function columns(): array
    {
        return [
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'accountNumber', 'label' => 'Account Number'],
            ['key' => 'accountName', 'label' => 'Account Holder'],
            ['key' => 'active', 'label' => 'Active', 'render' => static fn (BankAccount $item) => $item->active ? 'Yes' : 'No'],
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
}
