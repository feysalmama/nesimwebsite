<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\DonationIntent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Port of app/[locale]/donate/page.tsx, components/forms/DonateForm.tsx and
 * app/api/donate/route.ts.
 *
 * The API route's own note still applies: this records donor intent and nothing
 * more. The row it writes has no checkout session behind it, so wiring the
 * created id to a Chapa, Telebirr or bank transfer is still the next step, and
 * the form's footnote says as much to the visitor.
 */
class DonateController extends Controller
{
    /** The three options the React <select> offered, in the same order. */
    private const METHODS = ['telebirr' => 'Telebirr', 'bank' => 'Bank Transfer', 'card' => 'Card (Chapa)'];

    public function __invoke(): View
    {
        return view('site.donate', ['methods' => self::METHODS]);
    }

    /**
     * donationintent.amount is int(11), so `integer` rather than `numeric`:
     * z.coerce.number().positive() accepted 100.5 and handed Prisma a value the
     * column could not hold. `currency` and `status` are not in the form at all —
     * both have database defaults (ETB, pending) which is what the Next.js route
     * relied on by never naming them.
     */
    public function store(Request $request): RedirectResponse
    {
        DonationIntent::create($request->validate([
            'name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', 'max:191'],
            'phone' => ['nullable', 'string', 'max:191'],
            'amount' => ['required', 'integer', 'min:1'],
            'method' => ['required', 'string', 'in:'.implode(',', array_keys(self::METHODS))],
        ], [], [
            'name' => __('donate.form.name'),
            'email' => __('donate.form.email'),
            'phone' => __('donate.form.phone'),
            'amount' => __('donate.form.amount'),
            'method' => __('donate.form.method'),
        ]));

        return redirect()->back()->with('form_success', __('donate.form.success'));
    }
}
