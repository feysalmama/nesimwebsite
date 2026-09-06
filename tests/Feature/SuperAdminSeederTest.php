<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The bootstrap seeder, which is the only way into a fresh production install.
 *
 * It is tested for what it refuses to do as much as for what it does. A seeder
 * that runs on a live database on every deploy has one job that matters more
 * than creating the account: not disturbing the one that is already there. The
 * Next.js seed.js this replaces re-hashed the admin password on each run, so a
 * deploy quietly reset a password somebody had changed since — and it was only
 * noticed when nobody could sign in.
 *
 * Every test points config('nesim.admin.email') at a throwaway address. The
 * seeded admin@nesim.org is the only SUPER_ADMIN in this database and the rows
 * are live, so a test that used the real one would be asserting against data
 * that has to survive it. DatabaseTransactions rolls the rest back.
 */
class SuperAdminSeederTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * The address under test, unique per call so no two tests can collide on the
     * `email` unique index even if a rollback were ever to be missed.
     */
    private function email(): string
    {
        return 'zz.admin.'.Str::lower(Str::random(12)).'@nesim.org';
    }

    /**
     * Runs a seeder and hands back what it printed.
     *
     * Not called seed(): TestCase already has one, and it is public, so a
     * private override of it is a fatal error rather than a shadowed helper.
     */
    private function seedAndCapture(string $class = SuperAdminSeeder::class): string
    {
        Artisan::call('db:seed', ['--class' => $class, '--force' => true]);

        return Artisan::output();
    }

    /* ── Creating ─────────────────────────────────────────────────────────── */

    public function test_it_creates_a_super_admin_when_the_address_is_free(): void
    {
        $email = $this->email();

        config(['nesim.admin.email' => $email, 'nesim.admin.name' => 'Zz Bootstrap Admin']);

        $output = $this->seedAndCapture();

        $user = User::query()->where('email', $email)->first();

        $this->assertNotNull($user, 'the seeder wrote nothing');
        $this->assertSame(User::ROLE_SUPER_ADMIN, $user->role);
        $this->assertSame('Zz Bootstrap Admin', $user->name);
        $this->assertTrue($user->isSuperAdmin());
        $this->assertTrue($user->isStaff());
        $this->assertStringContainsString('Seeded SUPER_ADMIN '.$email, $output);
    }

    /**
     * The id has to be a cuid the seeder wrote itself. Prisma generated these
     * keys client-side, so MySQL has no default for the column and an INSERT
     * without one either fails outright or writes an empty string that the next
     * row then collides with.
     */
    public function test_it_writes_a_cuid_primary_key(): void
    {
        $email = $this->email();

        config(['nesim.admin.email' => $email]);

        $this->seedAndCapture();

        $id = (string) User::query()->where('email', $email)->value('id');

        $this->assertMatchesRegularExpression('/^c[0-9a-z]{24}$/', $id, 'the primary key is not a cuid');
    }

    /**
     * The regression this is really about.
     *
     * User assigns its key in a `creating` listener, and Model::withoutEvents()
     * — which the WithoutModelEvents trait wraps a whole seeding run in — stops
     * that listener firing. A seeder that relied on the hook would insert an
     * empty primary key whenever it was reached through such a parent, and pass
     * every test that ran it directly. So the events are turned off here on
     * purpose, the way DatabaseSeeder used to turn them off.
     */
    public function test_it_still_writes_a_key_with_model_events_suppressed(): void
    {
        $email = $this->email();

        config(['nesim.admin.email' => $email]);

        Model::withoutEvents(fn () => $this->seedAndCapture());

        $id = (string) User::query()->where('email', $email)->value('id');

        $this->assertMatchesRegularExpression(
            '/^c[0-9a-z]{24}$/',
            $id,
            'the key came from the creating listener, so suppressing model events leaves it empty',
        );
    }

    /**
     * Storing the hash is not the same as being able to sign in with the
     * password that was printed. LoginController calls verifyPassword() rather
     * than Auth::attempt(), because these hashes may carry bcryptjs's $2a$
     * prefix, so this asserts through the same method the login form does.
     */
    public function test_the_password_it_reports_signs_the_account_in(): void
    {
        $email = $this->email();

        config(['nesim.admin.email' => $email, 'nesim.admin.password' => 'zz-configured-secret']);

        $this->seedAndCapture();

        $user = User::query()->where('email', $email)->firstOrFail();

        $this->assertTrue(
            $user->verifyPassword('zz-configured-secret'),
            'the stored hash does not verify against the configured password',
        );
        $this->assertFalse($user->verifyPassword('anything-else'));
    }

    /**
     * No ADMIN_PASSWORD is the path a first deploy actually takes, so the
     * generated value has to be both printed and correct — an operator who
     * cannot read it back has no account.
     */
    public function test_it_generates_and_prints_a_password_when_none_is_configured(): void
    {
        $email = $this->email();

        config(['nesim.admin.email' => $email, 'nesim.admin.password' => null]);

        $output = $this->seedAndCapture();

        $this->assertMatchesRegularExpression(
            '/Generated password, shown once:\s*\n\s*\n\s+(\S+)/',
            $output,
            'the generated password was not printed',
        );

        preg_match('/Generated password, shown once:\s*\n\s*\n\s+(\S+)/', $output, $matches);

        $plain = $matches[1];

        // Letters and digits only: Str::password()'s symbols include `$`, which
        // phpdotenv would interpolate if the value were pasted into a .env line.
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9]{28}$/', $plain);

        $user = User::query()->where('email', $email)->firstOrFail();

        $this->assertTrue($user->verifyPassword($plain), 'the printed password does not match the stored hash');
    }

    /**
     * The mirror image: a password the operator supplied is already theirs, and
     * echoing it writes a live credential into terminal scrollback, a deploy log
     * and any CI output that captures the console.
     */
    public function test_it_does_not_echo_a_password_taken_from_configuration(): void
    {
        $email = $this->email();

        config(['nesim.admin.email' => $email, 'nesim.admin.password' => 'zz-configured-secret']);

        $output = $this->seedAndCapture();

        $this->assertStringNotContainsString('zz-configured-secret', $output);
        $this->assertStringContainsString('Password taken from ADMIN_PASSWORD', $output);
    }

    /* ── Refusing ─────────────────────────────────────────────────────────── */

    /**
     * The deploy case. Whatever is in the database already belongs to somebody,
     * so the seeder reports it and writes nothing — not the password, not the
     * role, not even the name.
     */
    public function test_it_leaves_an_existing_account_completely_alone(): void
    {
        $email = $this->email();

        $existing = User::factory()
            ->withPassword('zz-original-secret')
            ->create([
                'name' => 'Zz Someone Else',
                'email' => $email,
                'role' => User::ROLE_SUPER_ADMIN,
            ]);

        $before = [
            'id' => (string) $existing->id,
            'name' => (string) $existing->name,
            'role' => (string) $existing->role,
            'passwordHash' => (string) $existing->passwordHash,
            'createdAt' => (string) $existing->createdAt,
        ];

        // A different password and name, so an overwrite could not be missed.
        config([
            'nesim.admin.email' => $email,
            'nesim.admin.name' => 'Zz Seeder Name',
            'nesim.admin.password' => 'zz-replacement-secret',
        ]);

        $output = $this->seedAndCapture();

        $after = User::query()->where('email', $email)->firstOrFail();

        $this->assertSame($before['id'], (string) $after->id, 'a second account was created');
        $this->assertSame($before['name'], (string) $after->name, 'the name was overwritten');
        $this->assertSame($before['role'], (string) $after->role, 'the role was overwritten');
        $this->assertSame(
            $before['passwordHash'],
            (string) $after->passwordHash,
            'the password was re-hashed, which is what the old seed.js did on every deploy',
        );
        $this->assertSame($before['createdAt'], (string) $after->createdAt, 'the row was replaced');

        $this->assertTrue($after->verifyPassword('zz-original-secret'), 'the original password no longer works');
        $this->assertFalse($after->verifyPassword('zz-replacement-secret'), 'the configured password was applied');

        $this->assertSame(1, User::query()->where('email', $email)->count());
        $this->assertStringContainsString('left untouched', $output);
    }

    /**
     * Running it twice is what a deploy script does without thinking about it.
     */
    public function test_it_is_idempotent(): void
    {
        $email = $this->email();

        config(['nesim.admin.email' => $email, 'nesim.admin.password' => null]);

        $this->seedAndCapture();
        $this->seedAndCapture();
        $this->seedAndCapture();

        $this->assertSame(1, User::query()->where('email', $email)->count(), 're-running created more than one account');
    }

    /**
     * Promoting somebody is a decision for an operator, not for a seeder that
     * happened to be pointed at their address. The account below is an EDITOR
     * holding the configured email; the seeder must not quietly make it a
     * SUPER_ADMIN, and must say why it did not.
     */
    public function test_it_does_not_promote_an_existing_account_holding_a_lower_role(): void
    {
        $email = $this->email();

        User::factory()->create(['email' => $email, 'role' => User::ROLE_EDITOR]);

        config(['nesim.admin.email' => $email]);

        $output = $this->seedAndCapture();

        $this->assertSame(User::ROLE_EDITOR, (string) User::query()->where('email', $email)->value('role'));
        $this->assertStringContainsString('It is not a SUPER_ADMIN', $output);
    }

    public function test_it_refuses_to_write_without_a_usable_address(): void
    {
        $countBefore = User::query()->count();

        config(['nesim.admin.email' => 'not-an-address']);

        $output = $this->seedAndCapture();

        $this->assertSame($countBefore, User::query()->count(), 'a row was written for an unusable address');
        $this->assertStringContainsString('is not a usable address', $output);
    }

    /**
     * `php artisan migrate` on an empty database builds Laravel's stock `users`
     * table, whose password column is `password`. Writing into it would fail with
     * "Unknown column 'passwordHash'", which names the symptom and not the
     * mistake, so the seeder looks first and says what actually went wrong.
     *
     * The column cannot be dropped to reproduce this — the rows in it are live —
     * so the check is starved of its answer through the facade instead.
     */
    public function test_it_refuses_a_users_table_it_cannot_read(): void
    {
        $email = $this->email();

        config(['nesim.admin.email' => $email]);

        Schema::shouldReceive('hasColumn')->with('users', 'passwordHash')->andReturn(false);

        $output = $this->seedAndCapture();

        $this->assertNull(User::query()->where('email', $email)->first(), 'a row was written into the wrong schema');
        $this->assertStringContainsString('has no `passwordHash` column', $output);
        $this->assertStringContainsString('php artisan migrate', $output);
    }

    /* ── Reached the way production reaches it ────────────────────────────── */

    /**
     * `php artisan db:seed` with no --class runs DatabaseSeeder, which is what a
     * deploy script types. This is the whole chain, and it is the one that was
     * broken before: the stock body called User::factory()->create() with three
     * columns this table does not have.
     */
    public function test_the_default_seeder_reaches_it(): void
    {
        $email = $this->email();

        config(['nesim.admin.email' => $email, 'nesim.admin.password' => 'zz-through-database-seeder']);

        $this->seedAndCapture(DatabaseSeeder::class);

        $user = User::query()->where('email', $email)->first();

        $this->assertNotNull($user, 'DatabaseSeeder did not reach SuperAdminSeeder');
        $this->assertSame(User::ROLE_SUPER_ADMIN, $user->role);
        $this->assertMatchesRegularExpression('/^c[0-9a-z]{24}$/', (string) $user->id);
        $this->assertTrue($user->verifyPassword('zz-through-database-seeder'));
    }

    /**
     * The point of the whole exercise: the account it writes can open the panel
     * that is otherwise unreachable without a super admin.
     */
    public function test_the_seeded_account_can_open_the_users_panel(): void
    {
        $email = $this->email();

        config(['nesim.admin.email' => $email, 'nesim.admin.password' => 'zz-signs-in']);

        $this->seedAndCapture();

        $user = User::query()->where('email', $email)->firstOrFail();

        $this->actingAs($user)->get('/admin/users')->assertOk();
        $this->actingAs($user)->get('/admin')->assertOk();
    }
}
