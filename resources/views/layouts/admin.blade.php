<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{--
        The Next.js admin carried no robots meta and relied on next-auth
        redirecting anonymous visitors away from the page itself. /admin has
        real content behind it, so it is kept out of the index explicitly
        rather than by accident.
    --}}
    <meta name="robots" content="noindex, nofollow">

    <title>@yield('title', 'Dashboard') | Nesim CMS</title>

    {{-- Read by the upload handler in livewire/admin/resource-manager.blade.php,
         which posts to /admin/upload with fetch and so has to send the token
         itself rather than through a form field. --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/png" href="/favicon.png">

    @include('partials.fonts')

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{--
        No @livewireStyles / @livewireScripts here, on purpose. Livewire injects
        both automatically, but only into a response that actually rendered a
        component — so the login page and the dashboard ship no Livewire or
        Alpine bytes at all, and the module pages get them without this layout
        having to know which is which. Emitting the directives by hand would
        force that runtime onto every admin page.
    --}}
</head>
<body class="bg-canopy/40 font-body text-ink antialiased">
    {{-- app/admin/(protected)/layout.tsx --}}
    <div class="flex min-h-screen bg-canopy/40">
        @include('partials.admin.sidebar')

        <div class="flex min-h-screen flex-1 flex-col">
            @include('partials.admin.topbar')

            <main class="flex-1 p-6">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
