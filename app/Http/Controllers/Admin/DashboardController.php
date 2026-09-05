<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\ContactMessage;
use App\Models\DonationIntent;
use App\Models\Gallery;
use App\Models\Media;
use App\Models\MembershipApplication;
use App\Models\NewsPost;
use App\Models\Partner;
use App\Models\Program;
use App\Models\Project;
use App\Models\RegistrationSubmission;
use App\Models\Resource;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\VolunteerApplication;
use App\Support\AdminNav;
use Illuminate\View\View;

/**
 * Port of app/admin/(protected)/page.tsx.
 *
 * The original counted every row regardless of published state — these are CMS
 * totals, not site totals — so no scopePublished() is applied here.
 *
 * The React cards each carried a hardcoded href. Here they carry an AdminNav
 * slug, resolved to a URL by withUrls() below: only the modules listed in
 * AdminNav::SPECS exist in Laravel yet, and a slug with no spec becomes a null
 * URL, which the stat-card component renders as an inert card rather than a
 * link that would 404.
 */
class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'contentCards' => $this->withUrls([
                ['label' => 'Programs', 'value' => Program::count(), 'slug' => 'programs'],
                ['label' => 'Projects', 'value' => Project::count(), 'slug' => 'projects'],
                ['label' => 'Services', 'value' => Service::count(), 'slug' => 'services'],
                ['label' => 'Blog Posts', 'value' => BlogPost::count(), 'slug' => 'blog-posts'],
                ['label' => 'News Posts', 'value' => NewsPost::count(), 'slug' => 'news'],
                ['label' => 'Partners', 'value' => Partner::count(), 'slug' => 'partners'],
                ['label' => 'Galleries', 'value' => Gallery::count(), 'slug' => 'galleries'],
                ['label' => 'Resources', 'value' => Resource::count(), 'slug' => 'resources'],
                ['label' => 'Testimonials', 'value' => Testimonial::count(), 'slug' => 'testimonials'],
                ['label' => 'Media Files', 'value' => Media::count(), 'slug' => 'media'],
            ]),

            // Each of these is a queue with pending work, which is why the React
            // dashboard turned the number orange when it was above zero.
            'submissionCards' => $this->withUrls([
                ['label' => 'New volunteer applications', 'value' => VolunteerApplication::where('status', 'new')->count(), 'slug' => 'volunteers'],
                ['label' => 'New registrations', 'value' => RegistrationSubmission::where('status', 'new')->count(), 'slug' => 'registrations'],
                ['label' => 'Unread messages', 'value' => ContactMessage::where('status', 'new')->count(), 'slug' => 'messages'],
                ['label' => 'Pending donations', 'value' => DonationIntent::where('status', 'pending')->count(), 'slug' => 'donations'],
                ['label' => 'New membership apps', 'value' => MembershipApplication::where('status', 'new')->count(), 'slug' => 'membership-applications'],
            ]),
        ]);
    }

    /**
     * Resolve each card's slug to its admin URL, so the view stays markup only.
     *
     * @param  array<int, array{label: string, value: int, slug: string}>  $cards
     * @return array<int, array{label: string, value: int, slug: string, url: ?string}>
     */
    private function withUrls(array $cards): array
    {
        return array_map(
            static fn (array $card) => [...$card, 'url' => AdminNav::url($card['slug'])],
            $cards,
        );
    }
}
