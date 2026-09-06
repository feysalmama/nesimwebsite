<?php

namespace Database\Factories;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 *
 * Not the skeleton factory, because this is not the skeleton's `users` table.
 * There is no `email_verified_at`, no `remember_token` (User::$rememberTokenName
 * is null) and no `updated_at`, and the password column is `passwordHash`.
 * Emitting the three columns the skeleton assumes made every INSERT fail, which
 * is what left DatabaseSeeder broken.
 *
 * `id` is set here rather than left to User's `creating` listener for the reason
 * given on SuperAdminSeeder: Model::withoutEvents() stops that listener firing
 * and the column has no MySQL default.
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * The current password being used by the factory. Hashing once per process
     * rather than per row keeps a large create() from being dominated by bcrypt.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * The role is the schema's own default, EDITOR — the least privileged of the
     * five, so a test that forgets to ask for a role cannot accidentally write
     * an account that can reach /admin/users.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => BaseModel::cuid(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'passwordHash' => static::$password ??= Hash::make('password'),
            'role' => User::ROLE_EDITOR,
        ];
    }

    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => ['role' => User::ROLE_SUPER_ADMIN]);
    }

    /**
     * A known password, for a test that has to sign in as the user it made.
     * Not called password(): the static above already holds that name, and a
     * method and a property sharing it reads like a mistake even when it is not.
     */
    public function withPassword(string $plain): static
    {
        return $this->state(fn (array $attributes) => ['passwordHash' => Hash::make($plain)]);
    }
}
