@extends('layouts.site')

@section('title', __('nav.volunteer'))

@section('content')
    {{--
        app/[locale]/volunteer/page.tsx with components/forms/VolunteerForm.tsx
        folded in. Same reasoning as site/donate.blade.php: the "use client"
        wrapper existed to fetch /api/volunteer, and a POST to this URL needs no
        JavaScript at all.

        The React form used Field for skills, which renders an <input>, and a raw
        <textarea> for message. Both are kept as they were - skills is one line
        of comma-separated words, message is a paragraph.
    --}}
    <div class="py-16 sm:py-20">
        <x-container max="max-w-2xl">
            <x-section-heading :eyebrow="__('volunteer.eyebrow')" :title="__('volunteer.title')"
                               :subtitle="__('volunteer.subtitle')" align="center" />

            <div class="mt-10 rounded-2xl border border-leaf/15 bg-white p-6 shadow-sm sm:p-8">
                <div class="space-y-4">
                    <x-forms.feedback />

                    <form method="POST" action="{{ locale_path('volunteer') }}" class="space-y-4" data-submit-guard>
                        @csrf
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-forms.field :label="__('volunteer.form.name')" name="name" required />
                            <x-forms.field :label="__('volunteer.form.email')" name="email" type="email" required />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-forms.field :label="__('volunteer.form.phone')" name="phone" required />
                            <x-forms.field :label="__('volunteer.form.city')" name="city" />
                        </div>
                        <x-forms.field :label="__('volunteer.form.skills')" name="skills" />
                        <x-forms.textarea :label="__('volunteer.form.message')" name="message" />
                        <x-forms.submit :label="__('volunteer.form.submit')" />
                    </form>
                </div>
            </div>
        </x-container>
    </div>
@endsection
