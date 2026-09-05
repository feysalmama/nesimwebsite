<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\View\View;

/**
 * Port of app/[locale]/projects/page.tsx and projects/[id]/page.tsx.
 *
 * The detail route is keyed on the id, not the slug: projects/[id]/page.tsx
 * called getProject(id), a findUnique on the primary key, and project.slug is a
 * nullable column that ProjectCard never linked by. Every card the React site
 * rendered pointed at /projects/{id}, so those are the URLs already out there.
 */
class ProjectController extends Controller
{
    public function index(): View
    {
        return view('site.projects.index', [
            'projects' => Project::published()->get(),
        ]);
    }

    /** getProject(id) then notFound() when missing or unpublished. */
    public function show(string $id): View
    {
        $project = Project::findOrFail($id);

        abort_unless($project->published, 404);

        return view('site.projects.show', ['project' => $project]);
    }
}
