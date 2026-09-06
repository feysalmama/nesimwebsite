@extends('layouts.site')

@section('title', __('nav.testimonials'))

@section('content')

    <x-page-grid :eyebrow="__('testimonials.eyebrow')" :title="__('testimonials.title')"
                 :subtitle="__('testimonials.subtitle')" :count="$testimonials->count()"
                 empty="Testimonials will appear here once added in the CMS.">
        @foreach ($testimonials as $testimonial)
            <x-reveal :delay="$loop->index * 80">
                <x-cards.testimonial :name="$testimonial->name" :role="$testimonial->text('role')"
                                     :quote="$testimonial->text('quote')" :photoUrl="$testimonial->photoUrl" />
            </x-reveal>
        @endforeach
    </x-page-grid>
@endsection
