<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\VolunteerApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Port of app/[locale]/volunteer/page.tsx, components/forms/VolunteerForm.tsx
 * and app/api/volunteer/route.ts.
 */
class VolunteerController extends Controller
{
    public function __invoke(): View
    {
        return view('site.volunteer');
    }

    /**
     * name, email, phone and city are varchar(191); skills and message are TEXT.
     * `status` is not in the form — the column defaults to "new", which is what
     * the Next.js route relied on by never naming it.
     */
    public function store(Request $request): RedirectResponse
    {
        VolunteerApplication::create($request->validate([
            'name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', 'max:191'],
            'phone' => ['required', 'string', 'max:191'],
            'city' => ['nullable', 'string', 'max:191'],
            'skills' => ['nullable', 'string'],
            'message' => ['nullable', 'string'],
        ], [], [
            'name' => __('volunteer.form.name'),
            'email' => __('volunteer.form.email'),
            'phone' => __('volunteer.form.phone'),
            'city' => __('volunteer.form.city'),
            'skills' => __('volunteer.form.skills'),
            'message' => __('volunteer.form.message'),
        ]));

        return redirect()->back()->with('form_success', __('volunteer.form.success'));
    }
}
