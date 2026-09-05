<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Sign in | Nesim CMS</title>
    <link rel="icon" type="image/png" href="/favicon.png">

    @include('partials.fonts')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-cream font-body text-ink antialiased">
    {{--
        app/admin/login/page.tsx. Standalone, because the React login page sat
        outside the (protected) route group and so had no sidebar and no topbar.

        signIn("credentials", { redirect: false }) plus a client-side loading
        flag became an ordinary POST. The "Signing in…" state existed to cover a
        round trip the browser now makes itself; data-submit-guard disables the
        button on submit so a double click cannot send two requests.
    --}}
    <div class="flex min-h-screen items-center justify-center bg-canopy px-5">
        <div class="w-full max-w-sm rounded-2xl border border-leaf/15 bg-white p-8 shadow-sm">
            <div class="flex flex-col items-center text-center">
                <img src="/logo.png" alt="Nesim" width="56" height="56" class="h-14 w-14 rounded-full">
                <h1 class="mt-3 font-display text-xl font-semibold text-forest">Nesim CMS</h1>
                <p class="mt-1 text-sm text-stone">Sign in to manage site content.</p>
            </div>

            <form method="POST" action="{{ route('admin.login.store') }}" class="mt-6 space-y-4" data-submit-guard>
                @csrf

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-ink/80" for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                           autocomplete="username"
                           class="w-full rounded-xl border border-leaf/25 px-4 py-2.5 text-sm outline-none focus:border-sun">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-ink/80" for="password">Password</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password"
                           class="w-full rounded-xl border border-leaf/25 px-4 py-2.5 text-sm outline-none focus:border-sun">
                </div>

                {{--
                    setError("Invalid email or password.") in the React form.
                    LoginController puts that message — and the rate-limit one —
                    on the email key, so both land here. The password key can
                    only ever fail on `required`.
                --}}
                @if ($errors->any())
                    <div class="space-y-1">
                        @foreach ($errors->all() as $message)
                            <p class="text-sm text-danger">{{ $message }}</p>
                        @endforeach
                    </div>
                @endif

                <button type="submit"
                        class="w-full rounded-full bg-sun px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-sunlight disabled:opacity-60">
                    Sign In
                </button>
            </form>
        </div>
    </div>
</body>
</html>
