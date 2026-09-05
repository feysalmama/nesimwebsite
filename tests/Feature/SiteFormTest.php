<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\DonationIntent;
use App\Models\MembershipApplication;
use App\Models\MembershipCategory;
use App\Models\VolunteerApplication;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The five public submission forms.
 *
 * Each was three pieces in the Next.js app - a server page, a "use client" form
 * and a POST handler under app/api - and is one controller here with a GET and a
 * POST on the same path. What the split cost was the error handling: the React
 * forms replaced themselves with a single success line on a 200 and printed
 * "Something went wrong — please try again." for everything else, so a mistyped
 * field and a database outage read the same and neither said which box had
 * failed.
 *
 * These tests therefore pin the half that is new rather than the half that was
 * ported: that a row lands, that a rejected submission names its field, and that
 * nothing is written when one is.
 *
 * DatabaseTransactions, never RefreshDatabase: there are no migrations, the
 * schema is the one Prisma wrote and the rows are live. See phpunit.xml.
 */
class SiteFormTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * The five forms: the path they render on and post to, a payload that passes
     * validation, the table the row lands in, and one column to read it back by.
     *
     * @return array<string, array<int, mixed>>
     */
    public static function forms(): array
    {
        return [
            'contact' => [
                '/en/contact',
                ['name' => 'Zz Contact', 'email' => 'zz-contact@example.test', 'message' => 'Zz message'],
                'contactmessage',
                ['email' => 'zz-contact@example.test'],
            ],
            'donate' => [
                '/en/donate',
                ['name' => 'Zz Donor', 'email' => 'zz-donor@example.test', 'amount' => '500', 'method' => 'telebirr'],
                'donationintent',
                ['email' => 'zz-donor@example.test'],
            ],
            'membership' => [
                '/en/membership',
                [
                    'firstName' => 'Zz', 'lastName' => 'Member',
                    'email' => 'zz-member@example.test', 'phone' => '+251910000000',
                ],
                'membershipapplication',
                ['email' => 'zz-member@example.test'],
            ],
            'volunteer' => [
                '/en/volunteer',
                ['name' => 'Zz Volunteer', 'email' => 'zz-volunteer@example.test', 'phone' => '+251910000001'],
                'volunteerapplication',
                ['email' => 'zz-volunteer@example.test'],
            ],
            'register' => [
                '/en/register',
                ['fullName' => 'Zz Registrant', 'email' => 'zz-registrant@example.test', 'phone' => '+251910000002'],
                'registrationsubmission',
                ['email' => 'zz-registrant@example.test'],
            ],
        ];
    }

    /**
     * Just the paths, for the tests that only need to render the page.
     *
     * @return array<string, array<int, string>>
     */
    public static function formPages(): array
    {
        return array_map(static fn (array $case) => [$case[0]], self::forms());
    }

    /**
     * The referer is sent because every handler ends in redirect()->back().
     * Without it there is nothing to go back to and Laravel falls back to "/",
     * which would make the redirect target below a fact about the test rather
     * than about the form.
     */
    #[DataProvider('forms')]
    public function test_a_valid_submission_is_stored_and_returns_to_the_form(string $path, array $payload, string $table, array $expected): void
    {
        $this->post($path, $payload, ['referer' => $path])
            ->assertSessionHasNoErrors()
            ->assertRedirect($path);

        $this->assertDatabaseHas($table, $expected);
    }

    /**
     * An empty form comes back with its fields named, and writes nothing.
     *
     * This is the case the React forms could not distinguish from a server
     * failure - both produced one identical sentence under the button.
     */
    #[DataProvider('forms')]
    public function test_an_empty_submission_is_rejected_with_errors_and_stores_nothing(string $path, array $payload, string $table, array $expected): void
    {
        $before = (int) $this->app['db']->table($table)->count();

        $this->post($path, [], ['referer' => $path])
            ->assertSessionHasErrors()
            ->assertRedirect($path);

        $this->assertSame($before, (int) $this->app['db']->table($table)->count());
        $this->assertDatabaseMissing($table, $expected);
    }

    /**
     * The success line is the one from the locale the form was submitted in.
     *
     * Asserted through __() rather than against a literal, so the test follows
     * the language files instead of going stale the first time a wording changes.
     * Following the redirect is the point: the message is flashed, so it only
     * exists on the request after the POST, and that is the one a visitor sees.
     */
    public function test_the_success_message_is_the_one_for_the_locale_submitted_from(): void
    {
        $this->post('/am/contact', [
            'name' => 'Zz Amharic', 'email' => 'zz-am@example.test', 'message' => 'Zz',
        ], ['referer' => '/am/contact'])->assertRedirect('/am/contact');

        $this->get('/am/contact')
            ->assertOk()
            ->assertSee(__('contact.form.success', [], 'am'));
    }

    /**
     * A rejected submission has to hand back what was typed, or the visitor
     * re-enters the whole form to fix one box. old() in forms/field.blade.php is
     * what does it; this is the check that it is fed.
     */
    public function test_a_rejected_submission_renders_the_input_again(): void
    {
        $this->post('/en/volunteer', [
            'name' => 'Zz Kept Input', 'email' => 'not-an-email', 'phone' => '+251910000003',
        ], ['referer' => '/en/volunteer'])->assertSessionHasErrors('email');

        $this->get('/en/volunteer')
            ->assertOk()
            ->assertSee('value="Zz Kept Input"', false);
    }

    /**
     * A plain POST form has no CSRF protection unless the page emits a token, so
     * this is the whole of it. The React forms posted JSON to an API route that
     * Laravel's VerifyCsrfToken would never have seen.
     */
    #[DataProvider('formPages')]
    public function test_each_form_page_carries_a_csrf_token(string $path): void
    {
        $this->get($path)
            ->assertOk()
            ->assertSee('name="_token"', false);
    }

    /* ── The rules the Next.js API routes did not have ────────────────────── */

    /**
     * donationintent.amount is int(11). The zod schema was
     * z.coerce.number().positive(), which accepted 100.5 and handed Prisma a
     * value the column could not hold; `integer` turns that into a message under
     * the field instead of a 500 from MySQL.
     */
    public function test_a_fractional_donation_amount_is_rejected(): void
    {
        $this->post('/en/donate', [
            'name' => 'Zz Donor', 'email' => 'zz-fraction@example.test',
            'amount' => '100.5', 'method' => 'telebirr',
        ], ['referer' => '/en/donate'])->assertSessionHasErrors('amount');

        $this->assertDatabaseMissing('donationintent', ['email' => 'zz-fraction@example.test']);
    }

    /**
     * A method outside the three the <select> offers. The React form could only
     * submit one of them, but the endpoint accepted any string and the column is
     * varchar(191), so a crafted request wrote nonsense the CMS then had to show.
     */
    public function test_an_unknown_payment_method_is_rejected(): void
    {
        $this->post('/en/donate', [
            'name' => 'Zz Donor', 'email' => 'zz-method@example.test',
            'amount' => '100', 'method' => 'zz-crypto',
        ], ['referer' => '/en/donate'])->assertSessionHasErrors('method');
    }

    /**
     * The one rule app/api/membership/route.ts lacked: it passed the submitted
     * categoryId straight into the insert, so a form left open across a tier's
     * deletion came back as a 500 from the foreign key rather than as a message
     * under the field.
     */
    public function test_a_membership_category_that_does_not_exist_is_rejected(): void
    {
        $this->post('/en/membership', [
            'firstName' => 'Zz', 'lastName' => 'Member',
            'email' => 'zz-nocat@example.test', 'phone' => '+251910000004',
            'categoryId' => 'zz-no-such-category',
        ], ['referer' => '/en/membership'])->assertSessionHasErrors('categoryId');

        $this->assertDatabaseMissing('membershipapplication', ['email' => 'zz-nocat@example.test']);
    }

    /**
     * And a real tier goes in with its id intact, which is the other half of the
     * same rule - `exists` must not simply refuse everything.
     */
    public function test_a_membership_application_keeps_the_category_it_was_given(): void
    {
        $category = MembershipCategory::published()->first();

        $this->assertNotNull($category, 'no published membership category to apply under');

        $this->post('/en/membership', [
            'firstName' => 'Zz', 'lastName' => 'Member',
            'email' => 'zz-withcat@example.test', 'phone' => '+251910000005',
            'gender' => 'female', 'dob' => '1998-04-02', 'categoryId' => $category->id,
        ], ['referer' => '/en/membership'])->assertSessionHasNoErrors();

        $row = MembershipApplication::where('email', 'zz-withcat@example.test')->first();

        $this->assertNotNull($row);
        $this->assertSame($category->id, $row->categoryId);
        $this->assertSame('female', $row->gender);

        // dob is datetime(3) behind a `date` rule; the cast is what makes the
        // comparison below a date and not the raw column.
        $this->assertSame('1998-04-02', $row->dob?->format('Y-m-d'));
    }

    /**
     * The columns the forms never collected still take their database defaults,
     * which is what the Next.js routes relied on by never naming them: `status`
     * on all five tables and `currency` on donationintent.
     */
    public function test_the_defaults_the_forms_never_send_are_the_databases(): void
    {
        $this->post('/en/donate', [
            'name' => 'Zz Donor', 'email' => 'zz-defaults@example.test',
            'amount' => '250', 'method' => 'bank',
        ], ['referer' => '/en/donate'])->assertSessionHasNoErrors();

        $intent = DonationIntent::where('email', 'zz-defaults@example.test')->first();

        $this->assertNotNull($intent);
        $this->assertSame('ETB', $intent->currency);
        $this->assertSame('pending', $intent->status);
        $this->assertSame(250, $intent->amount);

        $this->post('/en/contact', [
            'name' => 'Zz Default', 'email' => 'zz-default-contact@example.test', 'message' => 'Zz',
        ], ['referer' => '/en/contact'])->assertSessionHasNoErrors();

        $this->assertSame(
            'new',
            ContactMessage::where('email', 'zz-default-contact@example.test')->value('status'),
        );
    }

    /**
     * A blank optional box arrives as an empty string from the browser and has to
     * be stored as null, not as "" - the column is nullable and the CMS filters
     * on it. ConvertEmptyStringsToNull is what does the conversion.
     */
    public function test_an_untouched_optional_field_is_stored_as_null(): void
    {
        $this->post('/en/volunteer', [
            'name' => 'Zz Blank', 'email' => 'zz-blank@example.test', 'phone' => '+251910000006',
            'city' => '', 'skills' => '', 'message' => '',
        ], ['referer' => '/en/volunteer'])->assertSessionHasNoErrors();

        $row = VolunteerApplication::where('email', 'zz-blank@example.test')->first();

        $this->assertNotNull($row);
        $this->assertNull($row->city);
        $this->assertNull($row->skills);
        $this->assertNull($row->message);
    }

    /**
     * varchar(191) is the limit on the short columns of all five tables. Without
     * max:191 a long value reached MySQL and came back as a 500; with it, the
     * visitor is told which box is too long.
     */
    public function test_a_value_longer_than_its_column_is_rejected_rather_than_truncated(): void
    {
        $this->post('/en/register', [
            'fullName' => str_repeat('Zz', 120), 'email' => 'zz-long@example.test', 'phone' => '+251910000007',
        ], ['referer' => '/en/register'])->assertSessionHasErrors('fullName');

        $this->assertDatabaseMissing('registrationsubmission', ['email' => 'zz-long@example.test']);
    }
}
