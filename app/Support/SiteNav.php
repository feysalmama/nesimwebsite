<?php

namespace App\Support;

/**
 * The public header navigation, ported from the NAV array in components/Navbar.tsx.
 *
 * `key` doubles as the translation key under the "nav" namespace in
 * lang/{locale}.json, exactly as useTranslations("nav") did in React.
 *
 * A group's href is null when it has no landing page of its own — the "Media"
 * entry is a pure dropdown in the original, and clicking its label toggles the
 * menu rather than navigating.
 */
final class SiteNav
{
    /**
     * @return array<int, array{key: string, href: ?string, children: array<int, array{key: string, href: string}>}>
     */
    public static function entries(): array
    {
        return [
            ['key' => 'home', 'href' => '', 'children' => []],

            [
                'key' => 'about',
                'href' => 'about',
                'children' => [
                    ['key' => 'leadership', 'href' => 'leadership'],
                    ['key' => 'impact', 'href' => 'impact'],
                    ['key' => 'testimonials', 'href' => 'testimonials'],
                ],
            ],

            [
                'key' => 'programsGroup',
                'href' => 'programs',
                'children' => [
                    ['key' => 'programs', 'href' => 'programs'],
                    ['key' => 'services', 'href' => 'services'],
                ],
            ],

            ['key' => 'projects', 'href' => 'projects', 'children' => []],

            [
                'key' => 'media',
                'href' => null,
                'children' => [
                    ['key' => 'news', 'href' => 'news'],
                    ['key' => 'blog', 'href' => 'blog'],
                    ['key' => 'gallery', 'href' => 'gallery'],
                    ['key' => 'resources', 'href' => 'resources'],
                ],
            ],

            [
                'key' => 'getInvolved',
                'href' => 'donate',
                'children' => [
                    ['key' => 'membership', 'href' => 'membership'],
                    ['key' => 'volunteer', 'href' => 'volunteer'],
                    ['key' => 'donate', 'href' => 'donate'],
                ],
            ],

            ['key' => 'contact', 'href' => 'contact', 'children' => []],
            ['key' => 'faq', 'href' => 'faq', 'children' => []],
        ];
    }

    /**
     * isGroupActive() from Navbar.tsx: a group lights up when its own landing
     * page is showing, or when any of its children is.
     */
    public static function isGroupActive(array $group, callable $isActive): bool
    {
        if ($group['href'] !== null && $isActive($group['href'])) {
            return true;
        }

        foreach ($group['children'] as $child) {
            if ($isActive($child['href'])) {
                return true;
            }
        }

        return false;
    }
}
