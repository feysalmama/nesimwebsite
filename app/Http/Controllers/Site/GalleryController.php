<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\View\View;

/**
 * Port of app/[locale]/gallery/page.tsx and gallery/[id]/page.tsx.
 *
 * Like projects and news, the detail route is keyed on the id: getGallery(id)
 * was a findUnique on the primary key and GalleryCard linked to /gallery/{id}.
 */
class GalleryController extends Controller
{
    /**
     * getGalleries() included the images so the card could show
     * g.images.length — a second query returning every row of every gallery,
     * read only to be counted. withCount() asks the database for the number
     * instead and returns the same figure.
     */
    public function index(): View
    {
        return view('site.gallery.index', [
            'galleries' => Gallery::published()
                ->withCount('images')
                ->orderByDesc('eventDate')
                ->get(),
        ]);
    }

    /** getGallery(id) then notFound() when missing or unpublished. */
    public function show(string $id): View
    {
        $gallery = Gallery::with(['images' => fn ($query) => $query->orderBy('order')])
            ->findOrFail($id);

        abort_unless($gallery->published, 404);

        return view('site.gallery.show', ['gallery' => $gallery]);
    }
}
