@extends('layouts.admin')

@section('title', $title)

@section('content')
    {{--
        Every ported module renders through this file. The heading, the table,
        the form drawer and the upload handler all live inside the component, so
        there is nothing module-specific left to put here.

        $component comes from the spec's component(), which is how one route
        serves a table, a submission queue, a sectioned singleton form and a
        media grid without any of them needing a view of their own.

        :resource is the AdminNav slug; the component resolves the spec from it
        on each request rather than being handed one, because a Livewire
        snapshot can only carry public scalar state.
    --}}
    @livewire($component, ['resource' => $resource])
@endsection
