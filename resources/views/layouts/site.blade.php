<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- viewport.themeColor from app/layout.tsx --}}
    <meta name="theme-color" content="#0F4C2A">

    {{--
        defaultMetadata() in lib/seo.ts. The title template was
        "%s | {orgName}" with the bare org name as the default.
    --}}
    @php
        $orgName = $settings->orgName ?: 'Nesim Foundation';
        $pageTitle = trim($__env->yieldContent('title'));
        $fullTitle = $pageTitle === '' ? $orgName : $pageTitle.' | '.$orgName;
        $description = trim($__env->yieldContent('description'))
            ?: ($settings->seoDescription
                ?: ($settings->tagline
                    ?: 'Empowering communities through education, sustainable development, and humanitarian aid.'));
    @endphp
    <title>{{ $fullTitle }}</title>
    <meta name="description" content="{{ $description }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $orgName }}">
    <meta property="og:title" content="{{ $fullTitle }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:image" content="{{ url($settings->logoOrDefault()) }}">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="icon" type="image/png" href="{{ $settings->faviconUrl ?: '/favicon.png' }}">
    <link rel="manifest" href="/manifest.json">

    {{--
        Inline and before the stylesheet on purpose. resources/js/app.js is
        loaded as a deferred module, so if it were the thing to add html.js the
        reveal rules would arrive after first paint and the page would visibly
        jump. Marking the document here means the hidden state is in place from
        the very first style recalculation — and a browser with scripting off
        never gets the class, so it just sees the content.
    --}}
    <script>document.documentElement.classList.add('js');</script>

    @include('partials.fonts')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-cream font-body text-ink antialiased">
    {{-- HtmlLangSetter + Navbar + main + Footer from app/[locale]/layout.tsx --}}
    @include('partials.navbar')

    <main>
        @yield('content')
    </main>

    @include('partials.footer')
</body>
</html>
