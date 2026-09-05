<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\View\View;

/**
 * Port of app/[locale]/services/page.tsx and services/[slug]/page.tsx.
 *
 * index() for the listing and show() for one service, rather than two invokable
 * controllers: the pair reads the same table and the pair of routes is the same
 * resource. The pages with no detail route stay single-action.
 */
class ServiceController extends Controller
{
    public function index(): View
    {
        return view('site.services.index', [
            'services' => Service::published()->get(),
        ]);
    }

    /**
     * getServiceBySlug() was findUnique({ where: { slug } }) with no published
     * filter, and the page then called notFound() when the row was missing *or*
     * unpublished. Both are a 404 here too, so a draft never confirms that its
     * slug exists — an unpublished service and a made-up one are
     * indistinguishable from outside, which is the behaviour worth keeping.
     */
    public function show(string $slug): View
    {
        $service = Service::where('slug', $slug)->firstOrFail();

        abort_unless($service->published, 404);

        return view('site.services.show', ['service' => $service]);
    }
}
