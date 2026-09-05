{{--
    components/admin/AdminTopbar.tsx, ported. `$adminUser` comes from the same
    view composer that feeds the sidebar.

    signOut({ callbackUrl: "/admin/login" }) became a POST to admin.logout.
    next-auth signed out from the browser and then redirected; a session cookie
    can only be cleared server-side, and a POST is what stops a prefetcher or a
    link scanner from logging an editor out by visiting a URL.
--}}
<header class="flex items-center justify-between border-b border-leaf/15 bg-white px-6 py-3">
    {{-- href="/en" in the React topbar; locale_path() is the same address
         without hardcoding which language the editor lands in. --}}
    <a href="{{ locale_path() }}" target="_blank" rel="noopener"
       class="text-sm font-medium text-leaf hover:text-forest">
        View live site ↗
    </a>

    <div class="flex items-center gap-4">
        <span class="text-sm text-ink/70">{{ $adminUser?->name }}</span>

        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit"
                    class="rounded-full border border-leaf/25 px-4 py-1.5 text-sm font-medium text-forest hover:bg-canopy">
                Sign out
            </button>
        </form>
    </div>
</header>
