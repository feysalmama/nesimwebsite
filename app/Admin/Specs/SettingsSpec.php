<?php

namespace App\Admin\Specs;

use App\Admin\SingletonSpec;
use App\Models\GlobalSettings;

/**
 * app/admin/(protected)/settings/page.tsx, ported.
 *
 * The SECTIONS constant from that file is sections() below, field for field and
 * label for label. Its layout rule was `grid gap-4 sm:grid-cols-2` with every
 * textarea spanning both columns, which is exactly what 'layout' => 'grid' plus
 * the editor's default span resolution produces — so no field here declares one.
 *
 * The one field type this module exists to justify is `color`: a native swatch
 * picker paired with a hex text input, both bound to the same value, as
 * renderField() did for primaryColor and accentColor.
 *
 * fallbacks() matters here and nowhere else. GlobalSettings::current() never
 * returns null — lib/content.ts fell back to hardcoded defaults so an unseeded
 * database still rendered — but those defaults live in PHP, not in MySQL, since
 * Prisma applies @default client-side. Without seeding the editor with them, the
 * first Save on a fresh install would write NULL over orgName and both colours
 * and the site would lose its name.
 */
final class SettingsSpec extends SingletonSpec
{
    public function title(): string
    {
        return 'Settings';
    }

    public function subtitle(): string
    {
        return 'Manage global site settings — branding, contact info, SEO, and more';
    }

    public function model(): string
    {
        return GlobalSettings::class;
    }

    public function rowId(): string
    {
        return GlobalSettings::SINGLETON_ID;
    }

    public function fallbacks(): array
    {
        return GlobalSettings::FALLBACKS;
    }

    public function sections(): array
    {
        return [
            [
                'title' => 'Organization',
                'layout' => 'grid',
                'fields' => [
                    ['name' => 'orgName', 'label' => 'Organization Name', 'type' => 'text'],
                    ['name' => 'shortName', 'label' => 'Short Name', 'type' => 'text'],
                    ['name' => 'tagline', 'label' => 'Tagline', 'type' => 'text'],
                ],
            ],
            [
                'title' => 'Branding',
                'layout' => 'grid',
                'fields' => [
                    ['name' => 'logoUrl', 'label' => 'Logo', 'type' => 'image'],
                    ['name' => 'darkLogoUrl', 'label' => 'Dark Logo', 'type' => 'image'],
                    ['name' => 'faviconUrl', 'label' => 'Favicon', 'type' => 'image'],
                    ['name' => 'primaryColor', 'label' => 'Primary Color', 'type' => 'color'],
                    ['name' => 'accentColor', 'label' => 'Accent Color', 'type' => 'color'],
                ],
            ],
            [
                'title' => 'Contact',
                'layout' => 'grid',
                'fields' => [
                    ['name' => 'phone', 'label' => 'Phone', 'type' => 'text'],
                    ['name' => 'email', 'label' => 'Email', 'type' => 'text'],
                    ['name' => 'address', 'label' => 'Address', 'type' => 'textarea'],
                    ['name' => 'mapEmbedUrl', 'label' => 'Map Embed URL', 'type' => 'text'],
                ],
            ],
            [
                'title' => 'Social Links',
                'layout' => 'grid',
                'fields' => [
                    ['name' => 'facebookUrl', 'label' => 'Facebook', 'type' => 'text'],
                    ['name' => 'twitterUrl', 'label' => 'Twitter / X', 'type' => 'text'],
                    ['name' => 'instagramUrl', 'label' => 'Instagram', 'type' => 'text'],
                    ['name' => 'youtubeUrl', 'label' => 'YouTube', 'type' => 'text'],
                    ['name' => 'linkedinUrl', 'label' => 'LinkedIn', 'type' => 'text'],
                    ['name' => 'telegramUrl', 'label' => 'Telegram', 'type' => 'text'],
                ],
            ],
            [
                'title' => 'Footer',
                'layout' => 'grid',
                'fields' => [
                    ['name' => 'footerText', 'label' => 'Footer Text', 'type' => 'textarea'],
                    ['name' => 'copyrightText', 'label' => 'Copyright Text', 'type' => 'text'],
                ],
            ],
            [
                'title' => 'SEO Defaults',
                'layout' => 'grid',
                'fields' => [
                    ['name' => 'seoTitle', 'label' => 'Default Title', 'type' => 'text'],
                    ['name' => 'seoDescription', 'label' => 'Default Description', 'type' => 'textarea'],
                    ['name' => 'seoKeywords', 'label' => 'Keywords (comma-separated)', 'type' => 'text'],
                ],
            ],
            [
                'title' => 'Calls to Action',
                'layout' => 'grid',
                'fields' => [
                    ['name' => 'donationLink', 'label' => 'Donation Link', 'type' => 'text'],
                    ['name' => 'membershipLink', 'label' => 'Membership Link', 'type' => 'text'],
                    ['name' => 'ctaText', 'label' => 'CTA Button Text', 'type' => 'text'],
                    ['name' => 'ctaUrl', 'label' => 'CTA Button URL', 'type' => 'text'],
                ],
            ],
            [
                'title' => 'Advanced',
                'layout' => 'grid',
                'fields' => [
                    ['name' => 'analyticsId', 'label' => 'Analytics ID (e.g. G-XXXX)', 'type' => 'text'],
                ],
            ],
        ];
    }

    /**
     * The React page had no validation at all — the PUT body went straight to
     * Prisma — so an empty Organization Name or a mistyped email saved happily
     * and the navbar rendered blank. Both columns are cheap to check and both
     * are read on every page of the site.
     */
    public function rules(): array
    {
        return [
            'form.orgName' => ['nullable', 'string', 'max:191'],
            'form.email' => ['nullable', 'email', 'max:191'],
            'form.primaryColor' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'form.accentColor' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ];
    }
}
