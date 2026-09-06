<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * What `php artisan db:seed` and `migrate --seed` run.
 *
 * The schema was written by Prisma, not by migrations. There is no `migrations`
 * table in this database, and the three files under database/migrations are the
 * untouched Laravel skeleton ones — they describe a different `users` table than
 * the app reads, so they have never run and must not. SuperAdminSeeder says so
 * plainly if somebody does run them; see its schemaIsReadable().
 *
 * The rows are live, so `db:seed` is not a reset and must not behave like one.
 * Every seeder reached from here has to be safe to re-run against production,
 * which is why the stock body's `User::factory()->create()` is gone: it wrote an
 * unconditional Test User on every call, and it wrote the three columns
 * Laravel's skeleton assumes (`email_verified_at`, `password`, `remember_token`)
 * that this `users` table does not have, so it failed before inserting anything.
 *
 * WithoutModelEvents is deliberately not used. These models assign their primary
 * key in a `creating` listener, and Model::withoutEvents() stops that listener
 * firing — every row seeded from here would be INSERTed with an empty `id`.
 * SuperAdminSeeder assigns its key explicitly as well, so it is correct either
 * way, but a future seeder should not have to know that.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(SuperAdminSeeder::class);
    }
}
