<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\MembershipApplication;
use App\Models\MembershipCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Port of app/[locale]/membership/page.tsx, its MembershipForm.tsx and
 * app/api/membership/route.ts.
 *
 * One controller rather than a form component because the <select> of tiers is
 * built from the same rows the cards above it are: getMembershipCategories() ran
 * once and the page mapped it twice.
 */
class MembershipController extends Controller
{
    public function __invoke(): View
    {
        $categories = MembershipCategory::published()->get();

        return view('site.membership', [
            'categories' => $categories,

            /*
             * The value => label map forms/select.blade.php wants, built here
             * because `name` is a three-language column: pluck('name', 'id')
             * would put the raw {"en":…,"am":…} document in the <option>.
             */
            'categoryOptions' => $categories->mapWithKeys(
                static fn (MembershipCategory $category) => [$category->id => $category->text('name')]
            )->all(),
        ]);
    }

    /**
     * The one rule the Next.js route did not have: `exists` on categoryId.
     * app/api/membership/route.ts passed the submitted id straight into the
     * insert, so a form left open across a tier's deletion came back as a 500
     * from the foreign key rather than as a message under the field.
     *
     * `dob` arrives as a yyyy-mm-dd string from <input type="date"> and is cast
     * to datetime(3) by the model; ConvertEmptyStringsToNull turns an untouched
     * box into the null the nullable column wants.
     */
    public function store(Request $request): RedirectResponse
    {
        MembershipApplication::create($request->validate([
            'firstName' => ['required', 'string', 'max:191'],
            'lastName' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', 'max:191'],
            'phone' => ['required', 'string', 'max:191'],
            'dob' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'in:male,female'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:191'],
            'occupation' => ['nullable', 'string', 'max:191'],
            'categoryId' => ['nullable', Rule::exists(MembershipCategory::class, 'id')],
            'motivation' => ['nullable', 'string'],
        ], [], [
            'firstName' => __('membership.firstName'),
            'lastName' => __('membership.lastName'),
            'email' => __('membership.email'),
            'phone' => __('membership.phone'),
            'dob' => __('membership.dob'),
            'gender' => __('membership.gender'),
            'address' => __('membership.address'),
            'city' => __('membership.city'),
            'occupation' => __('membership.occupation'),
            'categoryId' => __('membership.category'),
            'motivation' => __('membership.motivation'),
        ]));

        return redirect()->back()->with('form_success', __('membership.success'));
    }
}
