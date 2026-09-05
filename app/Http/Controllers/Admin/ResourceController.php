<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminNav;
use Illuminate\View\View;

/**
 * The one controller behind every ported CMS module.
 *
 * In the Next.js admin each module was a page.tsx that handed a field list and a
 * column list to the shared <ResourceManager>. Here the same split holds: this
 * controller resolves the slug to its spec and renders the shared Livewire
 * component, and nothing about the table itself is known at this level.
 *
 * Porting the next module is therefore a spec class plus one entry in
 * AdminNav::SPECS — no controller, no route and no view.
 */
class ResourceController extends Controller
{
    public function __invoke(string $resource): View
    {
        $spec = AdminNav::spec($resource);

        /*
         * Defensive only. routes/web.php constrains {resource} to the keys of
         * AdminNav::SPECS, so a slug reaching here without a spec means the two
         * lists have drifted apart — which should fail loudly, not silently
         * render an empty module.
         */
        abort_if($spec === null, 404);

        $spec = new $spec;

        /*
         * component() is what lets a module change its whole screen — a table,
         * a submission queue, a sectioned singleton form, a media grid — without
         * a route, a controller or a view of its own. The view hands it straight
         * to @livewire.
         */
        return view('admin.resource', [
            'resource' => $resource,
            'title' => $spec->title(),
            'component' => $spec->component(),
        ]);
    }
}
