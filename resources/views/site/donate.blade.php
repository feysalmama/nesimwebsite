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
        received - see the note on components/container.blade.php. The bank
        accounts get their own wider one, because a card has to hold a logo, a
        name and an account number, and two of them side by side is what makes
        the list scannable rather than a column to scroll.
    --}}
    <div class="py-16 sm:py-20">
        <x-container max="max-w-2xl">
            <x-section-heading :eyebrow="__('donate.eyebrow')" :title="__('donate.title')"
                               :subtitle="__('donate.subtitle')" align="center" />
        </x-container>

        {{--
            The rows from /admin/bank-accounts, above the form: a donor who picks
            "Bank Transfer" from the select below needs an account to send to
            before they fill it in, not after. scopeActive() has already applied
            the `active` filter and the `order` sort, so this is a straight loop.

            Unlike the footnote at the bottom of the page these four strings are
            in all three language files, as donate.banks.*. The Amharic and Afaan
            Oromoo wording is a first draft and worth a read by whoever owns the
            organisation's bank relationships.

            A bank with no logo gets a tile holding its initial rather than no
            tile at all, so the cards stay the same height whichever way round
            the editor uploads them.
        --}}
        @if ($banks->isNotEmpty())
            <x-container max="max-w-4xl">
                <div class="mt-12">
                    <h2 class="text-center font-display text-2xl font-semibold text-forest">
                        {{ __('donate.banks.title') }}
                    </h2>
                    <p class="mt-2 text-center text-sm text-stone">{{ __('donate.banks.subtitle') }}</p>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        @foreach ($banks as $bank)
                            <div class="rounded-2xl border border-leaf/15 bg-white p-5 shadow-sm">
                                <div class="flex items-center gap-3">
                                    @if ($bank->logoUrl)
                                        <div class="relative h-11 w-16 shrink-0 overflow-hidden">
                                            <img src="{{ $bank->logoUrl }}" alt="{{ $bank->name }}" loading="lazy"
                                                 class="absolute inset-0 h-full w-full object-contain">
                                        </div>
                                    @else
                                        <div class="flex h-11 w-16 shrink-0 items-center justify-center rounded-xl bg-canopy font-display text-lg font-semibold text-forest">
                                            {{ mb_substr($bank->name, 0, 1) }}
                                        </div>
                                    @endif

                                    <h3 class="font-display text-base font-semibold text-forest">{{ $bank->name }}</h3>
                                </div>

                                <dl class="mt-4 space-y-2 text-sm">
                                    <div>
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-leaf">
                                            {{ __('donate.banks.accountNumber') }}
                                        </dt>
                                        <dd class="mt-0.5 font-semibold text-forest">{{ $bank->accountNumber }}</dd>
                                    </div>

                                    {{-- Optional in BankAccountSpec, so a bank
                                         without one leaves no empty label. --}}
                                    @if ($bank->accountName)
                                        <div>
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-leaf">
                                                {{ __('donate.banks.accountName') }}
                                            </dt>
                                            <dd class="mt-0.5 text-stone">{{ $bank->accountName }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        @endforeach
                    </div>
                </div>
            </x-container>
        @endif

        <x-container max="max-w-2xl">
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
