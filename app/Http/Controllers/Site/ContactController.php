<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\GlobalSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Port of app/[locale]/contact/page.tsx, components/forms/ContactForm.tsx and
 * app/api/contact/route.ts.
 *
 * The three were a server component, a client component and an API route; here
 * they are one controller with a GET and a POST. The zod schema becomes
 * $request->validate(), and the fetch/JSON round trip becomes an ordinary form
 * post that redirects back — see components/forms/feedback.blade.php for what
 * the visitor gains from that.
 */
class ContactController extends Controller
{
    /** The map the page fell back to when globalsettings.mapEmbedUrl was empty. */
    private const FALLBACK_MAP = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3940.5!2d38.74!3d9.02!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zOcKwMDEnMTIuMCJOIDM4wrA0NCcyNC4wIkU!5e0!3m2!1sen!2set!4v1';

    public function __invoke(): View
    {
        $settings = GlobalSettings::current();

        return view('site.contact', [
            'address' => $settings->address ?: 'Addis Ababa, Ethiopia',
            'phone' => $settings->phone ?: '+251 91 234 5678',
            'email' => $settings->email ?: 'info@nesim.org',
            'mapEmbedUrl' => $settings->mapEmbedUrl ?: self::FALLBACK_MAP,
        ]);
    }

    /**
     * contactmessage.name and .email are varchar(191); subject and message are
     * TEXT and take no limit. Without max:191 a long address would reach MySQL
     * and come back as a 500 rather than as a message under the field.
     *
     * The table also has a `phone` column that ContactForm never collected, so
     * this form does not either — it is left null exactly as the Next.js route
     * left it.
     */
    public function store(Request $request): RedirectResponse
    {
        ContactMessage::create($request->validate([
            'name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', 'max:191'],
            'subject' => ['nullable', 'string'],
            'message' => ['required', 'string'],
        ], [], $this->attributeNames()));

        return redirect()->back()->with('form_success', __('contact.form.success'));
    }

    /**
     * The labels the visitor can see, so a failure names the box on screen
     * rather than the column behind it. Laravel would otherwise snake_case the
     * input name and print "The full name field is required."
     *
     * @return array<string, string>
     */
    private function attributeNames(): array
    {
        return [
            'name' => __('contact.form.name'),
            'email' => __('contact.form.email'),
            'subject' => __('contact.form.subject'),
            'message' => __('contact.form.message'),
        ];
    }
}
