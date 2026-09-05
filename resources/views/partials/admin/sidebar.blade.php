@php
    /*
     * components/admin/AdminSidebar.tsx, ported. `$navGroups` arrives from the
     * view composer in AppServiceProvider, which has already applied the role
     * filter the React component did with the session role and resolved each
     * item's URL.
     *
     * The one thing the React version did not have to express: most of these
     * modules still live in the Next.js admin, so an item with a null url is
     * rendered as inert text. The panel therefore shows its finished shape now
     * without a wall of 404s, and each entry becomes a link the moment its spec
     * is registered in AdminNav::SPECS.
     */
@endphp
<aside class="hidden w-64 shrink-0 border-r border-leaf/15 bg-white lg:block">
    <div class="flex items-center gap-2.5 border-b border-leaf/15 px-5 py-4">
        <img src="/logo.png" alt="Nesim" width="32" height="32" class="h-8 w-8 rounded-full">
        <span class="font-display text-base font-semibold text-forest">Nesim CMS</span>
    </div>

    <nav class="space-y-4 overflow-y-auto px-3 py-5" style="max-height: calc(100vh - 60px)">
        @foreach ($navGroups as $group)
            {{--
                Collapsed state lives in a data attribute rather than in Alpine.
                Livewire ships Alpine but only injects it into a response that
                rendered a component, and the dashboard and the login page render
                none — so a sidebar that needed Alpine would work on one admin
                page and sit frozen on the others. initAdminSidebar() in
                resources/js/app.js drives it instead, and the two visual states
                are rules in resources/css/app.css.
            --}}
            <div data-admin-group data-collapsed="false">
                <button type="button" data-admin-group-toggle aria-expanded="true"
                        class="flex w-full items-center justify-between px-3 text-[11px] font-semibold uppercase tracking-wide text-stone/70 hover:text-forest">
                    <span>{{ $group['section'] }}</span>

                    <svg class="admin-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <polyline points="6 9 12 15 18 9" />
                    </svg>
                </button>

                <div class="admin-group-body mt-1.5 space-y-0.5">
                    @foreach ($group['items'] as $item)
                        @php
                            // pathname === item.href in the React version: an exact
                            // match, so /admin does not stay lit on /admin/hero-slides.
                            $isActive = $item['url'] !== null
                                && request()->is(ltrim($item['url'], '/'));
                        @endphp

                        @if ($item['url'] !== null)
                            <a href="{{ $item['url'] }}"
                               @if ($isActive) aria-current="page" @endif
                               @class([
                                   'block rounded-lg px-3 py-2 text-sm font-medium transition',
                                   'bg-canopy text-forest' => $isActive,
                                   'text-ink/70 hover:bg-canopy/60' => ! $isActive,
                               ])>
                                {{ $item['label'] }}
                            </a>
                        @else
                            <span class="block cursor-not-allowed rounded-lg px-3 py-2 text-sm font-medium text-ink/30"
                                  title="Not ported yet — this module still opens in the Next.js admin">
                                {{ $item['label'] }}
                            </span>
                        @endif
                    @endforeach
                </div>
            </div>
        @endforeach
    </nav>
</aside>
