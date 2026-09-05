@extends('layouts.site')

@section('title', __('nav.membership'))

@section('content')
    {{--
        app/[locale]/membership/page.tsx with MembershipForm.tsx folded in.

        The React page called getMembershipCategories() once and mapped the same
        rows twice - once into the cards below the heading, once into the
        {id, name} list the form's <select> needed. MembershipController does the
        same: $categories drives the cards and $categoryOptions, already resolved
        into the active language, drives the <select>.

        This is the one page that cannot use x-page-grid: the grid is followed by
        a second section, so the heading is written out instead.

        The form is the longest on the site, and its three <select>s are the reason
        forms/select.blade.php takes a `placeholder`. Without it the category box
        would silently preselect the first tier and every application would arrive
        claiming a membership nobody chose.
    --}}
    <div class="py-16 sm:py-20">
        <x-container>
            <x-reveal>
                <x-section-heading :eyebrow="__('membership.eyebrow')" :title="__('membership.title')"
                                   :subtitle="__('membership.subtitle')" align="center" />
            </x-reveal>

            @if ($categories->isNotEmpty())
                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($categories as $category)
                        <x-reveal :delay="$loop->index * 80">
                            <div class="h-full rounded-2xl border border-leaf/15 bg-white p-6 shadow-sm">
                                <h3 class="font-display text-lg font-semibold text-forest">
                                    {{ $category->text('name') }}
                                </h3>

                                @if ($category->text('description') !== '')
                                    <p class="mt-2 text-sm leading-relaxed text-stone">
                                        {{ $category->text('description') }}
                                    </p>
                                @endif

                                {{-- benefits and requirements were optional in
                                     Prisma and stayed optional here; text() gives
                                     an empty string for a null column, which is
                                     what hides the block. --}}
                                @if ($category->text('benefits') !== '')
                                    <div class="mt-4">
                                        <h4 class="text-xs font-semibold uppercase tracking-wide text-leaf">
                                            {{ __('membership.benefits') }}
                                        </h4>
                                        <p class="mt-1 text-sm text-stone">{{ $category->text('benefits') }}</p>
                                    </div>
                                @endif

                                @if ($category->text('requirements') !== '')
                                    <div class="mt-3">
                                        <h4 class="text-xs font-semibold uppercase tracking-wide text-leaf">
                                            {{ __('membership.requirements') }}
                                        </h4>
                                        <p class="mt-1 text-sm text-stone">{{ $category->text('requirements') }}</p>
                                    </div>
                                @endif
                            </div>
                        </x-reveal>
                    @endforeach
                </div>
            @endif

            <div class="mx-auto mt-16 max-w-xl">
                <h2 class="font-display text-2xl font-semibold text-forest">{{ __('membership.applyTitle') }}</h2>
                <p class="mt-2 text-sm text-stone">{{ __('membership.applySubtitle') }}</p>

                <div class="mt-6 space-y-4">
                    <x-forms.feedback />

                    <form method="POST" action="{{ locale_path('membership') }}" class="space-y-4" data-submit-guard>
                        @csrf
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-forms.field :label="__('membership.firstName')" name="firstName" required />
                            <x-forms.field :label="__('membership.lastName')" name="lastName" required />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-forms.field :label="__('membership.email')" name="email" type="email" required />
                            <x-forms.field :label="__('membership.phone')" name="phone" required />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-forms.field :label="__('membership.dob')" name="dob" type="date" />
                            <x-forms.select :label="__('membership.gender')" name="gender"
                                            :placeholder="__('membership.selectOption')"
                                            :options="['male' => __('membership.male'), 'female' => __('membership.female')]" />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-forms.field :label="__('membership.city')" name="city" />
                            <x-forms.field :label="__('membership.occupation')" name="occupation" />
                        </div>
                        <x-forms.field :label="__('membership.address')" name="address" />
                        <x-forms.select :label="__('membership.category')" name="categoryId"
                                        :placeholder="__('membership.selectOption')" :options="$categoryOptions" />
                        <x-forms.textarea :label="__('membership.motivation')" name="motivation" :rows="4" />
                        <x-forms.submit :label="__('membership.submit')" />
                    </form>
                </div>
            </div>
        </x-container>
    </div>
@endsection
