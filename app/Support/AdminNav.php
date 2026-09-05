<?php

namespace App\Support;

use App\Admin\Specs\AboutContentSpec;
use App\Admin\Specs\ActivityLogSpec;
use App\Admin\Specs\BlogCategorySpec;
use App\Admin\Specs\BlogPostSpec;
use App\Admin\Specs\ContactMessageSpec;
use App\Admin\Specs\DonationIntentSpec;
use App\Admin\Specs\DownloadableResourceSpec;
use App\Admin\Specs\FaqItemSpec;
use App\Admin\Specs\GallerySpec;
use App\Admin\Specs\HeroSlideSpec;
use App\Admin\Specs\ImpactStatSpec;
use App\Admin\Specs\IslamicMessageSpec;
use App\Admin\Specs\LandingContentSpec;
use App\Admin\Specs\MembershipApplicationSpec;
use App\Admin\Specs\MembershipCategorySpec;
use App\Admin\Specs\MediaLibrarySpec;
use App\Admin\Specs\NewsCategorySpec;
use App\Admin\Specs\NewsPostSpec;
use App\Admin\Specs\PartnerSpec;
use App\Admin\Specs\PresidentMessageSpec;
use App\Admin\Specs\ProgramSpec;
use App\Admin\Specs\ProjectCategorySpec;
use App\Admin\Specs\ProjectSpec;
use App\Admin\Specs\RegistrationSubmissionSpec;
use App\Admin\Specs\ResourceCategorySpec;
use App\Admin\Specs\ServiceSpec;
use App\Admin\Specs\SettingsSpec;
use App\Admin\Specs\TagSpec;
use App\Admin\Specs\TeamMemberSpec;
use App\Admin\Specs\TestimonialSpec;
use App\Admin\Specs\UserSpec;
use App\Admin\Specs\VolunteerApplicationSpec;

/**
 * The CMS sidebar, ported from the NAV array in components/admin/AdminSidebar.tsx.
 *
 * One addition the React version did not need: knowledge of which modules have
 * actually been ported. Everything still renders in the sidebar — so the shape
 * of the finished panel is visible now — but only the ported ones are links,
 * and the rest are inert placeholders rather than URLs that would 404.
 */
final class AdminNav
{
    /**
     * The one list that makes a module real. Registering a spec here gives the
     * module a route (routes/web.php constrains the {resource} parameter to
     * these keys), turns its sidebar entry into a link and turns its dashboard
     * card into a link. No controller, route or view is written per module.
     *
     * The keys are the slugs the React admin's folder names produced, so every
     * bookmarked /admin URL keeps working. Grouped and ordered exactly as
     * groups() below lists them, which makes a missing entry visible by eye.
     *
     * 'dashboard' is absent because it is not a resource: it counts rows across
     * every table, so it has its own controller and view. Everything else in the
     * sidebar is here — 32 modules over four spec shapes and four components.
     *
     * @var array<string, class-string<\App\Admin\AdminSpec>>
     */
    private const SPECS = [
        // Content
        'programs' => ProgramSpec::class,
        'projects' => ProjectSpec::class,
        'services' => ServiceSpec::class,
        'blog-posts' => BlogPostSpec::class,
        'blog-categories' => BlogCategorySpec::class,
        'tags' => TagSpec::class,
        'news' => NewsPostSpec::class,
        'news-categories' => NewsCategorySpec::class,
        'project-categories' => ProjectCategorySpec::class,
        'faq' => FaqItemSpec::class,
        'impact' => ImpactStatSpec::class,
        'testimonials' => TestimonialSpec::class,
        'team' => TeamMemberSpec::class,
        'hero-slides' => HeroSlideSpec::class,
        'about-content' => AboutContentSpec::class,
        'landing-content' => LandingContentSpec::class,
        'islamic-messages' => IslamicMessageSpec::class,
        'president-message' => PresidentMessageSpec::class,
        'partners' => PartnerSpec::class,

        // Media
        'media' => MediaLibrarySpec::class,
        'galleries' => GallerySpec::class,
        'resources' => DownloadableResourceSpec::class,
        'resource-categories' => ResourceCategorySpec::class,

        // Membership
        'membership-categories' => MembershipCategorySpec::class,
        'membership-applications' => MembershipApplicationSpec::class,

        // Submissions
        'volunteers' => VolunteerApplicationSpec::class,
        'registrations' => RegistrationSubmissionSpec::class,
        'messages' => ContactMessageSpec::class,
        'donations' => DonationIntentSpec::class,

        // System
        'users' => UserSpec::class,
        'activity-logs' => ActivityLogSpec::class,
        'settings' => SettingsSpec::class,
    ];

    /**
     * @return array<int, array{section: string, roles: ?array<int, string>, items: array<int, array{label: string, slug: string}>}>
     */
    public static function groups(): array
    {
        return [
            [
                'section' => 'Overview',
                'roles' => null,
                'items' => [
                    ['label' => 'Dashboard', 'slug' => 'dashboard'],
                ],
            ],
            [
                'section' => 'Content',
                'roles' => null,
                'items' => [
                    ['label' => 'Programs', 'slug' => 'programs'],
                    ['label' => 'Projects', 'slug' => 'projects'],
                    ['label' => 'Services', 'slug' => 'services'],
                    ['label' => 'Blog Posts', 'slug' => 'blog-posts'],
                    ['label' => 'Blog Categories', 'slug' => 'blog-categories'],
                    ['label' => 'Tags', 'slug' => 'tags'],
                    ['label' => 'News', 'slug' => 'news'],
                    ['label' => 'News Categories', 'slug' => 'news-categories'],
                    ['label' => 'Project Categories', 'slug' => 'project-categories'],
                    ['label' => 'FAQ', 'slug' => 'faq'],
                    ['label' => 'Impact Stats', 'slug' => 'impact'],
                    ['label' => 'Testimonials', 'slug' => 'testimonials'],
                    ['label' => 'Team', 'slug' => 'team'],
                    ['label' => 'Hero Slides', 'slug' => 'hero-slides'],
                    ['label' => 'About Content', 'slug' => 'about-content'],
                    ['label' => 'Landing Page', 'slug' => 'landing-content'],
                    ['label' => 'Islamic Messages', 'slug' => 'islamic-messages'],
                    ['label' => "Chairman's Message", 'slug' => 'president-message'],
                    ['label' => 'Partners', 'slug' => 'partners'],
                ],
            ],
            [
                'section' => 'Media',
                'roles' => null,
                'items' => [
                    ['label' => 'Media Library', 'slug' => 'media'],
                    ['label' => 'Galleries', 'slug' => 'galleries'],
                    ['label' => 'Resources', 'slug' => 'resources'],
                    ['label' => 'Resource Categories', 'slug' => 'resource-categories'],
                ],
            ],
            [
                'section' => 'Membership',
                'roles' => ['SUPER_ADMIN', 'MEMBERSHIP_ADMIN', 'CONTENT_ADMIN'],
                'items' => [
                    ['label' => 'Categories', 'slug' => 'membership-categories'],
                    ['label' => 'Applications', 'slug' => 'membership-applications'],
                ],
            ],
            [
                'section' => 'Submissions',
                'roles' => null,
                'items' => [
                    ['label' => 'Volunteer Applications', 'slug' => 'volunteers'],
                    ['label' => 'Registrations', 'slug' => 'registrations'],
                    ['label' => 'Contact Messages', 'slug' => 'messages'],
                    ['label' => 'Donation Intents', 'slug' => 'donations'],
                ],
            ],
            [
                'section' => 'System',
                'roles' => ['SUPER_ADMIN'],
                'items' => [
                    ['label' => 'Staff Users', 'slug' => 'users'],
                    ['label' => 'Activity Logs', 'slug' => 'activity-logs'],
                    ['label' => 'Settings', 'slug' => 'settings'],
                ],
            ],
        ];
    }

    /**
     * The same role filter AdminSidebar applied to its NAV groups, with each
     * item's URL resolved up front so the sidebar partial stays markup only.
     * A null url is what tells the view to render a placeholder, not a link.
     *
     * @return array<int, array{section: string, roles: ?array<int, string>, items: array<int, array{label: string, slug: string, url: ?string}>}>
     */
    public static function groupsForRole(?string $role): array
    {
        $visible = array_filter(
            self::groups(),
            static fn (array $group) => $group['roles'] === null || in_array($role, $group['roles'], true),
        );

        return array_values(array_map(
            static fn (array $group) => [
                ...$group,
                'items' => array_map(
                    static fn (array $item) => [...$item, 'url' => self::url($item['slug'])],
                    $group['items'],
                ),
            ],
            $visible,
        ));
    }

    /**
     * @return array<string, class-string<\App\Admin\AdminSpec>>
     */
    public static function specs(): array
    {
        return self::SPECS;
    }

    /**
     * @return class-string<\App\Admin\AdminSpec>|null
     */
    public static function spec(string $slug): ?string
    {
        return self::SPECS[$slug] ?? null;
    }

    public static function isBuilt(string $slug): bool
    {
        return $slug === 'dashboard' || isset(self::SPECS[$slug]);
    }

    /** The admin URL for a slug, or null while that module is still on Next.js. */
    public static function url(string $slug): ?string
    {
        if (! self::isBuilt($slug)) {
            return null;
        }

        return $slug === 'dashboard' ? '/admin' : '/admin/'.$slug;
    }
}
