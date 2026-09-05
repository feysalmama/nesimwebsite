<?php

namespace App\Admin\Specs;

use App\Admin\ResourceSpec;
use App\Models\MembershipApplication;
use App\Models\MembershipCategory;

/**
 * app/admin/(protected)/membership-applications/page.tsx, ported.
 *
 * The one queue that was a ResourceManager page rather than a SubmissionTable,
 * because a membership admin corrects an applicant's details as well as moving
 * their status along — so it keeps the full create/edit form.
 *
 * Left at parity: dob, address and documents are columns the React form never
 * exposed. dob is a DateTime? and documents a JSON string, so neither would drop
 * straight into the existing field types.
 */
final class MembershipApplicationSpec extends ResourceSpec
{
    public function title(): string
    {
        return 'Membership Applications';
    }

    public function model(): string
    {
        return MembershipApplication::class;
    }

    public function fields(): array
    {
        return [
            ['name' => 'firstName', 'label' => 'First Name', 'type' => 'text', 'required' => true],
            ['name' => 'lastName', 'label' => 'Last Name', 'type' => 'text', 'required' => true],
            ['name' => 'email', 'label' => 'Email', 'type' => 'text', 'required' => true],
            ['name' => 'phone', 'label' => 'Phone', 'type' => 'text', 'required' => true],
            ['name' => 'gender', 'label' => 'Gender', 'type' => 'select', 'options' => ['male', 'female', 'other']],
            ['name' => 'city', 'label' => 'City', 'type' => 'text'],
            ['name' => 'occupation', 'label' => 'Occupation', 'type' => 'text'],
            [
                'name' => 'categoryId',
                'label' => 'Membership Category',
                'type' => 'select',
                // The English name: membershipcategory.name holds all three
                // languages, so plucking the column listed the tiers as JSON.
                'options' => static fn () => MembershipCategory::query()->orderBy('order')->get()
                    ->mapWithKeys(static fn (MembershipCategory $category) => [$category->id => $category->text('name', 'en')])
                    ->all(),
            ],
            ['name' => 'motivation', 'label' => 'Motivation', 'type' => 'textarea'],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'default' => 'new', 'options' => ['new', 'reviewing', 'approved', 'rejected']],
        ];
    }

    public function columns(): array
    {
        return [
            ['key' => 'firstName', 'label' => 'Name', 'render' => static fn (MembershipApplication $item) => trim($item->firstName.' '.$item->lastName)],
            ['key' => 'email', 'label' => 'Email'],
            ['key' => 'category', 'label' => 'Category', 'render' => static fn (MembershipApplication $item) => $item->category?->text('name', 'en') ?: '—'],
            ['key' => 'status', 'label' => 'Status'],
            ['key' => 'createdAt', 'label' => 'Date', 'render' => static fn (MembershipApplication $item) => $item->createdAt?->format('j M Y') ?? '—'],
        ];
    }

    public function with(): array
    {
        return ['category'];
    }

    public function readRoles(): ?array
    {
        return ['SUPER_ADMIN', 'MEMBERSHIP_ADMIN', 'CONTENT_ADMIN'];
    }
}
