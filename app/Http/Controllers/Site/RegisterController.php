<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\RegistrationSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Port of app/[locale]/register/page.tsx, components/forms/RegisterForm.tsx and
 * app/api/register/route.ts.
 *
 * Nothing on the site links to it — SiteNav has no register entry, matching the
 * NAV array in Navbar.tsx, and Footer.tsx listed volunteer, membership, donate,
 * contact and faq but not this. app/[locale]/register/page.tsx existed anyway,
 * so it stays reachable by direct URL at the same path it always was, which is
 * how anyone found it before.
 */
class RegisterController extends Controller
{
    public function __invoke(): View
    {
        return view('site.register');
    }

    /**
     * `program` stays a free-text box rather than a <select> over the program
     * table. RegisterForm rendered a plain Field for it and registrationsubmission
     * .program is varchar(191), so someone writing "the literacy thing in Bahir
     * Dar" was always a valid submission; narrowing it to published rows would
     * reject that and is a product decision rather than a port.
     */
    public function store(Request $request): RedirectResponse
    {
        RegistrationSubmission::create($request->validate([
            'fullName' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', 'max:191'],
            'phone' => ['required', 'string', 'max:191'],
            'program' => ['nullable', 'string', 'max:191'],
            'city' => ['nullable', 'string', 'max:191'],
            'notes' => ['nullable', 'string'],
        ], [], [
            'fullName' => __('register.form.name'),
            'email' => __('register.form.email'),
            'phone' => __('register.form.phone'),
            'program' => __('register.form.program'),
            'city' => __('register.form.city'),
            'notes' => __('register.form.notes'),
        ]));

        return redirect()->back()->with('form_success', __('register.form.success'));
    }
}
