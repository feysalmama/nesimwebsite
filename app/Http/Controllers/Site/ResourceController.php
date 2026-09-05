<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use Illuminate\View\View;

/**
 * Port of app/[locale]/resources/page.tsx.
 *
 * Named ResourceController like its admin counterpart, so routes/web.php
 * imports one of them under an alias — both are already needed in the same
 * file and a rename here would only move the collision.
 */
class ResourceController extends Controller
{
    public function __invoke(): View
    {
        return view('site.resources', [
            'resources' => Resource::published()
                ->with('category')
                ->orderByDesc('publishedAt')
                ->get(),
        ]);
    }
}
