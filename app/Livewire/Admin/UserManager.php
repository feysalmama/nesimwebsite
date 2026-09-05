<?php

namespace App\Livewire\Admin;

use App\Admin\AdminSpec;
use App\Models\ActivityLog;
use App\Models\User;
use App\Support\AdminNav;
use App\Support\ColumnLimits;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

/**
 * app/admin/(protected)/users/UsersClient.tsx, ported.
 *
 * The React screen could never be reached: its page component redirected away
 * unless the session role was exactly "ADMIN", a value the Role enum does not
 * contain, so the sidebar link bounced every super admin back to the dashboard.
 * This is the first time the module has been openable.
 *
 * What changed and why:
 *
 *  - Editing exists. PUT /api/admin/users/[id] was written and nothing called it,
 *    so a wrong role or a forgotten password could only be fixed by deleting the
 *    account and recreating it — which also destroyed its activity-log history's
 *    author. The drawer below serves that route.
 *  - `setError(body.error)` and `alert()` became real validation with per-field
 *    messages. The POST route validated with zod but answered everything with one
 *    "Invalid input" string, so an editor who typed a bad email was told only
 *    that something was wrong.
 *  - Two lockout guards the React routes did not have. "You cannot delete your own
 *    account" is kept, and joined by one on demotion: changing the last remaining
 *    SUPER_ADMIN's role would leave nobody able to open this screen again, and the
 *    only way back would be a direct database write.
 *  - The password goes through User::setPassword(), never through mass
 *    assignment. Every model here has an empty $guarded, so binding a property bag
 *    straight to fill() would have written a column named "password" that nothing
 *    reads and left "passwordHash" untouched.
 */
class UserManager extends Component
{
    /** The AdminNav slug, always "users". The spec is resolved from it. */
    public string $resource = '';

    /**
     * Drawer values: name, email, role and password. `password` is never written
     * to the model as a column — save() hashes it through User::setPassword()
     * instead. It is null rather than "" when editing and the field was left
     * blank, because `nullable` passes on a real null only and Livewire's update
     * payload never goes through ConvertEmptyStringsToNull.
     *
     * @var array<string, string|null>
     */
    public array $form = [];

    public ?string $editingId = null;

    public bool $showForm = false;

    /** Result of the last create or update. */
    public ?string $notice = null;

    /** A refusal that is not about one field: the two lockout guards. */
    public ?string $error = null;

    private ?AdminSpec $spec = null;

    /**
     * The five roles, in ascending order of access, with the wording UsersClient's
     * dropdown used. Declared here rather than read from the database because the
     * column is a MySQL ENUM: a value not in this list would be rejected by the
     * server, and a value in the list but not in the enum would be truncated.
     *
     * @var array<string, string>
     */
    private const ROLES = [
        User::ROLE_VIEWER => 'Viewer — read-only access',
        User::ROLE_EDITOR => 'Editor — manages content',
        User::ROLE_MEMBERSHIP_ADMIN => 'Membership Admin — manages membership',
        User::ROLE_CONTENT_ADMIN => 'Content Admin — manages all content',
        User::ROLE_SUPER_ADMIN => 'Super Admin — full access',
    ];

    public function mount(string $resource): void
    {
        $this->resource = $resource;
    }

    /* ── Opening and closing the drawer ───────────────────────────────────── */

    public function openCreate(): void
    {
        $this->editingId = null;
        $this->form = ['name' => '', 'email' => '', 'role' => User::ROLE_EDITOR, 'password' => ''];
        $this->notice = null;
        $this->error = null;
        $this->showForm = true;

        $this->resetErrorBag();
    }

    public function openEdit(string $id): void
    {
        $user = $this->newQuery()->findOrFail($id);

        $this->editingId = (string) $user->getKey();
        $this->form = [
            'name' => (string) $user->name,
            'email' => (string) $user->email,
            'role' => (string) $user->role,
            // Deliberately not the stored hash: the field is a plaintext box, and
            // a value in it means "set this password", not "keep the old one".
            'password' => '',
        ];
        $this->notice = null;
        $this->error = null;
        $this->showForm = true;

        $this->resetErrorBag();
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->editingId = null;
        $this->form = [];
        $this->error = null;
        $this->resetErrorBag();
    }

    /* ── Saving and deleting ──────────────────────────────────────────────── */

    public function save(): void
    {
        $this->assertRoleAllowed($this->spec()->writeRoles());

        /*
         * Empty becomes null before validating rather than after, for the reason
         * given on $form: a blank password box on an edit means "keep the current
         * one", and `min:6` would otherwise refuse the empty string that arrives.
         */
        $this->form = [
            ...$this->form,
            'name' => trim((string) ($this->form['name'] ?? '')),
            'email' => trim((string) ($this->form['email'] ?? '')),
            'password' => ($this->form['password'] ?? '') === '' ? null : $this->form['password'],
        ];

        $validated = $this->validate($this->validationRules(), [], $this->attributes());

        $user = $this->editingId === null
            ? new User
            : User::query()->findOrFail($this->editingId);

        /*
         * Checked before anything is written, not after: a demotion that leaves
         * the site without a super admin cannot be undone from the panel.
         */
        if ($this->removesLastSuperAdmin($user, $validated['form']['role'])) {
            $this->error = 'This is the last super admin. Promote someone else first.';
            $this->addError('form.role', $this->error);

            return;
        }

        $this->error = null;

        $user->name = $validated['form']['name'];
        $user->email = $validated['form']['email'];
        $user->role = $validated['form']['role'];

        // Blank means "leave it alone" when editing, which is the behaviour the
        // React PUT had — `if (body.password)` — and what makes the field safe to
        // leave empty on every edit.
        if (! empty($validated['form']['password'])) {
            $user->setPassword((string) $validated['form']['password']);
        }

        $isNew = ! $user->exists;

        $user->save();

        $this->recordActivity($isNew ? 'create' : 'update', (string) $user->getKey());

        $this->notice = ($isNew ? $user->name.' can now sign in.' : $user->name.' updated.');

        $this->closeForm();
    }

    public function delete(string $id): void
    {
        $this->assertRoleAllowed($this->spec()->writeRoles());

        $user = User::query()->findOrFail($id);

        if ($this->isSelf((string) $user->getKey())) {
            $this->error = 'You cannot delete your own account.';

            return;
        }

        if ($this->removesLastSuperAdmin($user, null)) {
            $this->error = 'That is the last super admin. Promote someone else first.';

            return;
        }

        $this->error = null;

        $name = (string) $user->name;

        $user->delete();

        $this->recordActivity('delete', $id);

        $this->notice = $name."'s access was removed.";
    }

    /* ── Shape of the module, for the view ────────────────────────────────── */

    /**
     * @return Collection<int, User>
     */
    public function items(): Collection
    {
        // Unpaginated, as the React GET was: this is a list of staff, which is a
        // handful of rows by nature, and a page two of it would mean something
        // had gone badly wrong.
        return $this->newQuery()->get();
    }

    /**
     * @return array<string, string>
     */
    public function roles(): array
    {
        return self::ROLES;
    }

    /** The label for a role the enum holds but ROLES does not, rather than "". */
    public function roleLabel(?string $role): string
    {
        return self::ROLES[$role] ?? (string) $role;
    }

    public function isSelf(string $id): bool
    {
        return (string) auth()->id() === $id;
    }

    public function canWrite(): bool
    {
        $user = auth()->user();

        if ($user === null || ! $user->isStaff()) {
            return false;
        }

        $roles = $this->spec()->writeRoles();

        return $roles === null || in_array($user->role, $roles, true);
    }

    public function render(): View
    {
        /*
         * In render() and not only in mount(): render() runs on the page load and
         * on every subsequent update, so a replayed snapshot cannot read or write
         * a module the signed-in role was never allowed to open. The React page
         * checked once, on the server, before the client component took over.
         */
        $this->assertRoleAllowed($this->spec()->readRoles());

        return view('livewire.admin.user-manager', [
            'spec' => $this->spec(),
            'users' => $this->items(),
            'canWrite' => $this->canWrite(),
        ]);
    }

    /* ── Internals ────────────────────────────────────────────────────────── */

    private function spec(): AdminSpec
    {
        if ($this->spec === null) {
            $class = AdminNav::spec($this->resource);

            abort_if($class === null, 404);

            $this->spec = new $class;
        }

        return $this->spec;
    }

    /**
     * @return Builder<User>
     */
    private function newQuery(): Builder
    {
        return User::query()
            ->orderBy($this->spec()->orderBy(), $this->spec()->orderDirection());
    }

    /**
     * Length ceilings come from the columns themselves, for the reason given on
     * App\Support\ColumnLimits. The password has none: it is hashed, so what the
     * column stores bears no relation to what was typed, and a long passphrase
     * must not be refused because bcrypt's input is capped at 72 bytes.
     *
     * @return array<string, array<int, mixed>>
     */
    private function validationRules(): array
    {
        $table = (new User)->getTable();

        return [
            'form.name' => [
                'required', 'string',
                'max:'.ColumnLimits::get($table, 'name'),
            ],
            'form.email' => [
                'required', 'email',
                'max:'.ColumnLimits::get($table, 'email'),
                // The POST route answered a duplicate with a bare 409; this says
                // which field and whose address it is.
                Rule::unique($table, 'email')->ignore($this->editingId, 'id'),
            ],
            'form.role' => ['required', Rule::in(array_keys(self::ROLES))],
            'form.password' => [
                // zod's min(6) on create; optional on edit, where blank means
                // "keep the current password".
                $this->editingId === null ? 'required' : 'nullable',
                'string', 'min:6', 'max:200',
            ],
        ];
    }

    /**
     * Without these the messages read "The form.email field is required."
     *
     * @return array<string, string>
     */
    private function attributes(): array
    {
        return [
            'form.name' => 'name',
            'form.email' => 'email',
            'form.role' => 'role',
            'form.password' => 'password',
        ];
    }

    /**
     * True when the change about to be made would leave the `user` table with no
     * SUPER_ADMIN at all.
     *
     * $newRole is null for a delete. Counting only after excluding the user being
     * changed keeps the two cases in one query: anyone else holding the role means
     * the change is safe, whatever this row does next.
     */
    private function removesLastSuperAdmin(User $user, ?string $newRole): bool
    {
        $isSuperAdmin = (string) $user->getAttribute('role') === User::ROLE_SUPER_ADMIN;

        // A viewer being renamed is not a lockout risk, and neither is a create.
        if (! $isSuperAdmin || ! $user->exists) {
            return false;
        }

        if ($newRole !== null && $newRole === User::ROLE_SUPER_ADMIN) {
            return false;
        }

        $others = User::query()
            ->where('role', User::ROLE_SUPER_ADMIN)
            ->where('id', '!=', $user->getKey())
            ->exists();

        return ! $others;
    }

    /**
     * requireAdmin() from lib/adminAuth.ts. Not named authorize(): Component pulls
     * in AuthorizesRequests, which owns that name for Gate checks.
     *
     * @param  array<int, string>|null  $roles
     */
    private function assertRoleAllowed(?array $roles): void
    {
        $user = auth()->user();

        abort_if($user === null || ! $user->isStaff(), 403);

        if ($roles !== null) {
            abort_unless(in_array($user->role, $roles, true), 403);
        }
    }

    /**
     * logActivity() from lib/activityLog.ts, with the same empty-catch trade-off
     * the React routes made: an audit row must never fail an admin's save, but it
     * must not disappear without a trace either.
     */
    private function recordActivity(string $action, ?string $entityId): void
    {
        try {
            ActivityLog::create([
                'userId' => auth()->id(),
                'action' => $action,
                'entity' => $this->spec()->entity(),
                'entityId' => $entityId,
            ]);
        } catch (\Throwable $e) {
            Log::error('[admin] activity log write failed', [
                'action' => $action,
                'entity' => $this->spec()->entity(),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
