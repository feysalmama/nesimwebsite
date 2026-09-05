{{--
    app/admin/(protected)/users/UsersClient.tsx, in Blade.

    One root element, as Livewire requires. The "Loading…" row is gone because the
    table is server-rendered; wire:loading dims it instead.

    The drawer shape, the class strings and the "+ Add Staff" button come from
    resource-manager.blade.php rather than from UsersClient, which inlined its
    create form above the table and had no edit form at all: one panel, one look.

    The Remove button is absent on the signed-in admin's own row, as it was in
    UsersClient, and UserManager::delete() refuses the same thing from the server
    side — hiding the button stops the mistake, it is not what prevents it.
--}}
<div>
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="min-w-0">
            <h1 class="font-display text-2xl font-semibold text-forest">{{ $spec->title() }}</h1>

            <p class="mt-1 text-sm text-stone">
                {{ $users->count() }} staff account{{ $users->count() === 1 ? '' : 's' }}
            </p>
        </div>

        @if ($canWrite)
            <button type="button" wire:click="openCreate"
                    class="rounded-full bg-sun px-5 py-2 text-sm font-semibold text-white hover:bg-sunlight">
                + Add Staff
            </button>
        @endif
    </div>

    @if ($error !== null)
        <p class="mt-5 rounded-xl border border-danger/25 bg-danger/5 px-3.5 py-2 text-sm text-danger" role="alert">
            {{ $error }}
        </p>
    @endif

    @if ($notice !== null)
        <p class="mt-5 rounded-xl border border-leaf/25 bg-leaf/5 px-3.5 py-2 text-sm text-forest" role="status">
            {{ $notice }}
        </p>
    @endif

    <div class="mt-6 overflow-x-auto rounded-2xl border border-leaf/15 bg-white shadow-sm transition-opacity"
         wire:loading.class="opacity-50">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-leaf/15 bg-canopy/40 text-xs font-semibold uppercase tracking-wide text-stone">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Role</th>
                    <th class="px-4 py-3">Added</th>

                    @if ($canWrite)
                        <th class="px-4 py-3 text-right">Actions</th>
                    @endif
                </tr>
            </thead>

            <tbody class="divide-y divide-leaf/10">
                @foreach ($users as $user)
                    <tr wire:key="user-{{ $user->getKey() }}" class="hover:bg-canopy/20">
                        <td class="px-4 py-3">
                            <span class="text-ink/90">{{ $user->name }}</span>

                            @if ($this->isSelf((string) $user->getKey()))
                                <span class="ml-2 rounded-full bg-leaf/15 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-forest">
                                    You
                                </span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-ink/70">{{ $user->email }}</td>

                        <td class="px-4 py-3">
                            {{--
                                The short role name rather than the dropdown's
                                sentence: a table of them reads as a column of
                                prose otherwise, and the explanation is on the
                                form where the role is chosen.
                            --}}
                            <span class="inline-block rounded-full bg-canopy px-2.5 py-0.5 text-xs font-medium text-forest">
                                {{ str((string) $user->role)->replace('_', ' ')->title() }}
                            </span>
                        </td>

                        <td class="whitespace-nowrap px-4 py-3 text-xs text-stone">
                            {{ $user->createdAt?->format('j M Y') }}
                        </td>

                        @if ($canWrite)
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <button type="button" wire:click="openEdit('{{ $user->getKey() }}')"
                                        class="mr-3 text-sm font-medium text-leaf hover:text-forest">
                                    Edit
                                </button>

                                @unless ($this->isSelf((string) $user->getKey()))
                                    <button type="button"
                                            wire:click="delete('{{ $user->getKey() }}')"
                                            wire:confirm="Remove this staff member's access?"
                                            class="text-sm font-medium text-danger hover:opacity-75">
                                        Remove
                                    </button>
                                @endunless
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($showForm)
        <div class="fixed inset-0 z-50 flex items-start justify-end bg-ink/40 backdrop-blur-sm">
            <div class="h-full w-full max-w-lg overflow-y-auto bg-white shadow-xl">
                <form wire:submit="save" class="flex h-full flex-col">
                    <div class="flex items-center justify-between border-b border-leaf/15 px-6 py-4">
                        <h2 class="font-display text-lg font-semibold text-forest">
                            {{ $editingId !== null ? 'Edit Staff Member' : 'Add Staff' }}
                        </h2>

                        <button type="button" wire:click="closeForm" class="text-stone hover:text-ink" aria-label="Close">
                            ✕
                        </button>
                    </div>

                    <div class="flex-1 space-y-5 px-6 py-5">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-ink/80" for="um-name">
                                Name <span class="text-danger" aria-hidden="true">*</span>
                            </label>

                            <input id="um-name" type="text" wire:model="form.name" autocomplete="off"
                                   class="w-full rounded-xl border border-leaf/25 px-3.5 py-2 text-sm outline-none focus:border-sun">

                            @error('form.name')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-ink/80" for="um-email">
                                Email <span class="text-danger" aria-hidden="true">*</span>
                            </label>

                            <input id="um-email" type="email" wire:model="form.email" autocomplete="off"
                                   class="w-full rounded-xl border border-leaf/25 px-3.5 py-2 text-sm outline-none focus:border-sun">

                            @error('form.email')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror

                            <p class="mt-1.5 text-xs text-stone">
                                This is what they sign in with.
                            </p>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-ink/80" for="um-role">
                                Role <span class="text-danger" aria-hidden="true">*</span>
                            </label>

                            <select id="um-role" wire:model="form.role"
                                    class="w-full rounded-xl border border-leaf/25 px-3.5 py-2 text-sm outline-none focus:border-sun">
                                @foreach ($this->roles() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>

                            @error('form.role')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-ink/80" for="um-password">
                                @if ($editingId === null)
                                    Temporary password <span class="text-danger" aria-hidden="true">*</span>
                                @else
                                    New password
                                @endif
                            </label>

                            <input id="um-password" type="password" wire:model="form.password"
                                   autocomplete="new-password"
                                   class="w-full rounded-xl border border-leaf/25 px-3.5 py-2 text-sm outline-none focus:border-sun">

                            @error('form.password')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror

                            <p class="mt-1.5 text-xs text-stone">
                                @if ($editingId === null)
                                    At least 6 characters. Share it over a separate channel and ask
                                    them to change it at their first sign-in.
                                @else
                                    Leave blank to keep the current password.
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="border-t border-leaf/15 px-6 py-4">
                        <button type="submit" wire:loading.attr="disabled" wire:target="save"
                                class="rounded-full bg-forest px-6 py-2.5 text-sm font-semibold text-white hover:bg-leaf disabled:opacity-60">
                            {{ $editingId !== null ? 'Save Changes' : 'Create Staff Account' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
