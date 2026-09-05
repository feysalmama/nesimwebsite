@extends('layouts.site')

@section('title', __('nav.register'))

@section('content')
    {{--
        app/[locale]/register/page.tsx with components/forms/RegisterForm.tsx
        folded in. Identical in shape to site/volunteer.blade.php, because the two
        React forms differed only in their field names and in what they posted to.

        The label says "Full name" while the input is named fullName, and the
        controller validates fullName - both are what the React form did, so a
        submission from either build lands in the same column.
    --}}
    <div class="py-16 sm:py-20">
        <x-container max="max-w-2xl">
            <x-section-heading :eyebrow="__('register.eyebrow')" :title="__('register.title')"
                               :subtitle="__('register.subtitle')" align="center" />

            <div class="mt-10 rounded-2xl border border-leaf/15 bg-white p-6 shadow-sm sm:p-8">
                <div class="space-y-4">
                    <x-forms.feedback />

                    <form method="POST" action="{{ locale_path('register') }}" class="space-y-4" data-submit-guard>
                        @csrf
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-forms.field :label="__('register.form.name')" name="fullName" required />
                            <x-forms.field :label="__('register.form.email')" name="email" type="email" required />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-forms.field :label="__('register.form.phone')" name="phone" required />
                            <x-forms.field :label="__('register.form.city')" name="city" />
                        </div>
                        <x-forms.field :label="__('register.form.program')" name="program" />
                        <x-forms.textarea :label="__('register.form.notes')" name="notes" />
                        <x-forms.submit :label="__('register.form.submit')" />
                    </form>
                </div>
            </div>
        </x-container>
    </div>
@endsection
