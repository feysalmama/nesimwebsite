@extends('layouts.site')

@section('title', __('nav.donate'))

@section('content')
    {{--
        app/[locale]/donate/page.tsx with components/forms/DonateForm.tsx folded
        in. The React form was a separate "use client" component only because it
        had to fetch /api/donate; as an ordinary POST to the same URL there is
        nothing left for it to own, so it lives here like every other field on
        the site.

        The two-column grid the React form wrapped its Fields in is kept rather
        than flattened, because pairing name/email and phone/amount is what made
        the five-field form fit above the fold.

        max="max-w-2xl" is the narrow column the React page asked for and never
        received - see the note on components/container.blade.php.
    --}}
    <div class="py-16 sm:py-20">
        <x-container max="max-w-2xl">
            <x-section-heading :eyebrow="__('donate.eyebrow')" :title="__('donate.title')"
                               :subtitle="__('donate.subtitle')" align="center" />

            <div class="mt-10 rounded-2xl border border-leaf/15 bg-white p-6 shadow-sm sm:p-8">
                <div class="space-y-4">
                    <x-forms.feedback />

                    <form method="POST" action="{{ locale_path('donate') }}" class="space-y-4" data-submit-guard>
                        @csrf
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-forms.field :label="__('donate.form.name')" name="name" required />
                            <x-forms.field :label="__('donate.form.email')" name="email" type="email" required />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-forms.field :label="__('donate.form.phone')" name="phone" />
                            <x-forms.field :label="__('donate.form.amount')" name="amount" type="number" required />
                        </div>
                        {{-- $methods comes from DonateController, which holds the
                             same three values the React <select> hardcoded and
                             validates against, so the two cannot drift apart. --}}
                        <x-forms.select :label="__('donate.form.method')" name="method" :options="$methods" required />
                        <x-forms.submit :label="__('donate.form.submit')" />
                    </form>
                </div>
            </div>

            {{--
                Carried over verbatim from the React page, which hardcoded this
                sentence in English rather than putting it in the three language
                files. There is no donate.note key to call, and inventing an
                Amharic and Oromo wording for a payment-security statement is not
                a porting decision - it needs whoever owns the Chapa agreement.
            --}}
            <p class="mt-4 text-center text-xs text-stone">
                Card payments are processed securely via Chapa. Telebirr and bank transfer details are confirmed by our team after submission.
            </p>
        </x-container>
    </div>
@endsection
