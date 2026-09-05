<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\FaqItem;
use Illuminate\View\View;

/**
 * Port of app/[locale]/faq/page.tsx.
 *
 * The React page mapped the rows onto { question, answer } and fell back to a
 * literal PLACEHOLDER_FAQS when the table was empty. Both happen here so the
 * accordion component receives the same resolved shape either way and does not
 * have to know about locale columns.
 */
class FaqController extends Controller
{
    /** PLACEHOLDER_FAQS from faq/page.tsx, shown when faqitem is empty. */
    private const PLACEHOLDER_FAQS = [
        ['id' => 'f1', 'question' => 'How can I volunteer with Nesim?', 'answer' => 'Fill out the volunteer form on our Volunteer page and our team will follow up within a week.'],
        ['id' => 'f2', 'question' => 'How are donations used?', 'answer' => 'Donations directly fund classroom materials, teacher training, and community development projects.'],
        ['id' => 'f3', 'question' => 'Do you operate outside Addis Ababa?', 'answer' => 'Yes — our programs run across multiple regions of Ethiopia. See the Projects page for active locations.'],
    ];

    public function __invoke(): View
    {
        $items = FaqItem::published()->get()
            ->map(static fn (FaqItem $faq) => [
                'id' => $faq->id,
                'question' => $faq->text('question'),
                'answer' => $faq->text('answer'),
            ])
            ->all();

        return view('site.faq', [
            'faqs' => $items ?: self::PLACEHOLDER_FAQS,
        ]);
    }
}
