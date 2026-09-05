<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Program;
use Illuminate\View\View;

/**
 * Port of app/[locale]/programs/page.tsx.
 *
 * getPrograms() was findMany({ where: { published: true }, orderBy: { order } }),
 * which is exactly Program::published().
 */
class ProgramController extends Controller
{
    public function __invoke(): View
    {
        return view('site.programs', [
            'programs' => Program::published()->get(),
        ]);
    }
}
