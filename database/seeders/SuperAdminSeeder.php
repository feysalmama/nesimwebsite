<?php

namespace Database\Seeders;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Creates the account a fresh production install cannot be reached without.
 *
 * The `users` table ships empty on a new server, and the only screen that can
 * create a staff account is /admin/users — which UserManager opens for a
 * SUPER_ADMIN alone. With no super admin there is no way in to make one, so the
 * bootstrap has to come from the console.
 *
 * Three rules shape it:
 *
 * It creates, never updates. An address that already exists is reported and
 * left exactly as it was — password, role and name untouched. The Next.js
 * seed.js this replaces re-hashed and overwrote the admin password on every
 * run, so a deploy silently reset an account somebody had since changed.
 * Re-running this against a live database is inert.
 *
 * No password is ever written down here. ADMIN_PASSWORD unset means a random
 * one is generated and printed once; a literal default would be a credential
 * published to everyone who can read the repository.
 *
 * The primary key is assigned explicitly rather than left to User's `creating`
 * listener. Model::withoutEvents() stops that listener firing, and a seeder
 * reached through a parent that uses WithoutModelEvents would otherwise INSERT
 * an empty `id` — the column has no MySQL default, because Prisma generated
 * these ids client-side.
 */
class SuperAdminSeeder extends Seeder
{
    /**
     * Length of a generated password. Letters and digits only, on purpose:
     * Str::password()'s symbol set includes `$`, `\` and `|`, and this value is
     * meant to be pasted into a .env line or a shell. phpdotenv interpolates
     * `${...}`, so a generated `$` would corrupt the very file it was copied
     * into. 28 characters of a 62-symbol alphabet is around 166 bits, which
     * gives up nothing by dropping the punctuation.
     */
    private const GENERATED_LENGTH = 28;

    public function run(): void
    {
        $email = Str::lower(trim((string) config('nesim.admin.email')));
        $name = trim((string) config('nesim.admin.name'));

        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $this->say('Nothing seeded: ADMIN_EMAIL ('.($email === '' ? 'unset' : $email).') is not a usable address.');

            return;
        }

        if (! $this->schemaIsReadable()) {
            return;
        }

        $existing = User::query()->where('email', $email)->first();

        if ($existing instanceof User) {
            $this->reportExisting($existing, $email);

            return;
        }

        /*
         * Config first, random second. The generated value is the common path:
         * it exists only for as long as this console output does, so nothing
         * durable holds a copy of it.
         */
        $configured = (string) config('nesim.admin.password');
        $generated = $configured === '';
        $plain = $generated ? Str::password(self::GENERATED_LENGTH, symbols: false) : $configured;

        $user = new User;

        $user->id = BaseModel::cuid();
        $user->name = $name === '' ? 'Nesim Admin' : $name;
        $user->email = $email;
        $user->role = User::ROLE_SUPER_ADMIN;
        $user->setPassword($plain);

        $user->save();

        $this->reportCreated($user, $plain, $generated);
    }

    /**
     * Whether this is a `users` table the application can actually read.
     *
     * `php artisan migrate` here builds Laravel's stock one — bigint `id`,
     * `password`, `email_verified_at`, `remember_token` — from
     * 0001_01_01_000000_create_users_table.php. This database has no
     * migrations table at all: its 35 tables were written by Prisma and come
     * back with a dump. So an operator who follows the usual `migrate --seed`
     * deploy incantation against an empty database gets the wrong schema, and
     * the insert then fails with "Unknown column 'passwordHash' in 'field
     * list'", which describes the symptom and not the mistake.
     *
     * `passwordHash` alone discriminates the two: no stock Laravel schema has
     * ever used that name, and every Prisma one here does.
     */
    private function schemaIsReadable(): bool
    {
        if (Schema::hasColumn('users', 'passwordHash')) {
            return true;
        }

        $this->say('Nothing seeded: `users` has no `passwordHash` column, so it is not the table this app reads.');
        $this->say("  `php artisan migrate` creates Laravel's stock schema here, not this one. Restore the");
        $this->say('  database dump first, then run `php artisan db:seed` on its own.');

        return false;
    }

    /**
     * The account is already there, so the only useful output is what it is and
     * — when it is not a super admin — that the seeder deliberately did not
     * promote it. Changing the role of an existing person's account is not this
     * seeder's decision to make, but leaving the operator without a way forward
     * would be worse than saying nothing.
     */
    private function reportExisting(User $user, string $email): void
    {
        $this->say($email.' already exists as '.$user->name.' ('.$user->role.') — left untouched.');

        if ($user->isSuperAdmin()) {
            return;
        }

        $others = User::query()->where('role', User::ROLE_SUPER_ADMIN)->exists();

        if ($others) {
            $this->say('  It is not a SUPER_ADMIN. Sign in as one and change the role under /admin/users.');

            return;
        }

        $this->say('  It is not a SUPER_ADMIN and no other account is one, so /admin/users is unreachable.');
        $this->say('  Promote it explicitly rather than by re-running this seeder:');
        $this->say("    php artisan tinker --execute=\"App\\Models\\User::where('email', '".$email."')->update(['role' => 'SUPER_ADMIN']);\"");
    }

    private function reportCreated(User $user, string $plain, bool $generated): void
    {
        $this->say('Seeded SUPER_ADMIN '.$user->email.' ('.$user->name.').');

        if (! $generated) {
            // The operator supplied this value, so echoing it back adds a second
            // place it is written down and nothing they did not already have.
            $this->say('  Password taken from ADMIN_PASSWORD.');

            return;
        }

        $this->say('  Generated password, shown once:');
        $this->say('');
        $this->say('    '.$plain);
        $this->say('');
        $this->say('  It is not stored anywhere else. Sign in at /admin/login and change it');
        $this->say('  under /admin/users, or set ADMIN_PASSWORD and re-run on a fresh database.');
    }

    /**
     * Console output that survives being called from somewhere with no console.
     * Seeder::$command is null when a seeder is instantiated directly, which is
     * how the tests reach it; a seeder that threw there would be untestable.
     */
    private function say(string $line): void
    {
        $this->command?->line($line);
    }
}
