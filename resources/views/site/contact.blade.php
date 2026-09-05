@extends('layouts.site')

@section('title', __('nav.contact'))

@section('content')
    {{--
        app/[locale]/contact/page.tsx. The three inline SVGs are carried over
        verbatim: strokeWidth becomes stroke-width and nothing else changes,
        because React's camelCase attributes were always this HTML underneath.

        The address, phone, email and map URL arrive resolved from
        ContactController with the same literal fallbacks the React page had.
    --}}
    <div class="py-16 sm:py-20">
        <x-container>
            <x-section-heading :eyebrow="__('contact.eyebrow')" :title="__('contact.title')"
                               :subtitle="__('contact.subtitle')" align="center" />

            <div class="mt-12 grid gap-10 lg:grid-cols-[1fr_1.2fr]">
                {{-- Contact details and the embedded map --}}
                <div class="space-y-8">
                    <dl class="space-y-5 text-sm">
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-canopy text-forest">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" /><circle cx="12" cy="10" r="3" /></svg>
                            </div>
                            <div>
                                <dt class="font-semibold text-forest">{{ __('contact.address') }}</dt>
                                <dd class="text-stone">{{ $address }}</dd>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-canopy text-forest">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" /></svg>
                            </div>
                            <div>
                                <dt class="font-semibold text-forest">{{ __('contact.phone') }}</dt>
                                <dd><a href="tel:{{ $phone }}" class="text-stone transition hover:text-forest">{{ $phone }}</a></dd>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-canopy text-forest">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" /><polyline points="22,6 12,13 2,6" /></svg>
                            </div>
                            <div>
                                <dt class="font-semibold text-forest">{{ __('contact.email') }}</dt>
                                <dd><a href="mailto:{{ $email }}" class="text-stone transition hover:text-forest">{{ $email }}</a></dd>
                            </div>
                        </div>
                    </dl>

                    <div class="overflow-hidden rounded-2xl border border-leaf/15 bg-canopy">
                        <iframe src="{{ $mapEmbedUrl }}" width="100%" height="280" style="border: 0" allowfullscreen
                                loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Our Location"
                                class="block"></iframe>
                    </div>
                </div>

                {{-- The form. ContactForm.tsx, as an ordinary POST. --}}
                <div class="rounded-2xl border border-leaf/15 bg-white p-6 shadow-sm sm:p-8">
                    <div class="space-y-4">
                        <x-forms.feedback />

                        <form method="POST" action="{{ locale_path('contact') }}" class="space-y-4" data-submit-guard>
                            @csrf
                            <x-forms.field :label="__('contact.form.name')" name="name" required />
                            <x-forms.field :label="__('contact.form.email')" name="email" type="email" required />
                            <x-forms.field :label="__('contact.form.subject')" name="subject" />
                            <x-forms.textarea :label="__('contact.form.message')" name="message" :rows="5" required />
                            <x-forms.submit :label="__('contact.form.submit')" />
                        </form>
                    </div>
                </div>
            </div>
        </x-container>
    </div>
@endsection
