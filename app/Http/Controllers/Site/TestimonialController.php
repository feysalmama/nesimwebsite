<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\View\View;

/**
 * Port of app/[locale]/testimonials/page.tsx.
 *
 * The homepage shows three of these through limit(3); this page shows all of
 * them, which is the only difference between the two queries.
 */
class TestimonialController extends Controller
{
    public function __invoke(): View
    {
        return view('site.testimonials', [
            'testimonials' => Testimonial::published()->get(),
        ]);
    }
}
