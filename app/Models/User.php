<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

/**
 * The staff account. Table is `users` - Laravel's default for a model called
 * User, so there is no $table override here. It was `user`, singular, because
 * Prisma derived the name from the model; the table has since been renamed to
 * match the convention and the three InnoDB foreign keys that pointed at it
 * (activitylog.userId, blogpost.authorId, media.uploadedBy) followed.
 *
 * The columns are still the ones Prisma created, so three things stay
 * unconventional:
 *
 * The password column is `passwordHash` (bcryptjs, $2a$ prefix, cost 10) rather
 * than Laravel's conventional `password`, so getAuthPassword() is overridden and
 * verification goes through verifyPassword() below — not Hash::check() directly.
 *
 * Timestamps are `createdAt` only, hence the CREATED_AT/UPDATED_AT constants.
 *
 * There is no remember_token column, so $rememberTokenName is nulled out to
 * stop Laravel looking for one.
 *
 * It also cannot extend BaseModel — Authenticatable has to be the parent — so
 * the cuid assignment BaseModel does for the other 33 tables is repeated in
 * booted() below. Without it an INSERT writes an empty primary key, because
 * Prisma generated these ids client-side and MySQL has no default for the column.
 */
class User extends Authenticatable
{
    const CREATED_AT = 'createdAt';

    const UPDATED_AT = null;

    public const ROLE_SUPER_ADMIN = 'SUPER_ADMIN';

    public const ROLE_CONTENT_ADMIN = 'CONTENT_ADMIN';

    public const ROLE_MEMBERSHIP_ADMIN = 'MEMBERSHIP_ADMIN';

    public const ROLE_EDITOR = 'EDITOR';

    public const ROLE_VIEWER = 'VIEWER';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected $hidden = ['passwordHash'];

    protected $rememberTokenName = null;

    protected function casts(): array
    {
        return ['createdAt' => 'datetime'];
    }

    /** Assign a cuid primary key on insert unless the caller supplied one. */
    protected static function booted(): void
    {
        static::creating(function (self $model) {
            $key = $model->getKeyName();

            if (! $model->getAttribute($key)) {
                $model->setAttribute($key, BaseModel::cuid());
            }
        });
    }

    public function getAuthPassword(): string
    {
        return (string) $this->passwordHash;
    }

    /**
     * Verify a plaintext password against the stored hash.
     *
     * This cannot simply be Hash::check(). The hashes were written by bcryptjs
     * in the Next.js app, which emits the $2a$ prefix. PHP's password_get_info()
     * does not classify $2a$ as bcrypt, so Laravel's BcryptHasher throws
     * "This password does not use the Bcrypt algorithm" instead of verifying.
     *
     * $2a$ and $2y$ are the same corrected bcrypt algorithm — the split only
     * exists to mark an old crypt_blowfish 8-bit bug — so normalising the prefix
     * lets every existing hash verify unchanged, with no data migration.
     *
     * Because Auth::attempt() goes through Hash::check() internally, the login
     * controller calls this directly and then Auth::login().
     */
    public function verifyPassword(string $plain): bool
    {
        $hash = (string) $this->passwordHash;

        if ($hash === '') {
            return false;
        }

        if (str_starts_with($hash, '$2a$')) {
            $hash = '$2y$'.substr($hash, 4);
        }

        return Hash::check($plain, $hash);
    }

    /**
     * Hash a new password for storage. Produces a $2y$ hash, which bcryptjs in
     * the Next.js app also accepts, so both stacks stay interchangeable.
     */
    public function setPassword(string $plain): void
    {
        $this->passwordHash = Hash::make($plain);
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class, 'uploadedBy');
    }

    public function blogPosts(): HasMany
    {
        return $this->hasMany(BlogPost::class, 'authorId');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'userId');
    }

    /**
     * Mirrors requireAdmin() in lib/adminAuth.ts.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    /**
     * Mirrors requireStaff(): any of the five roles counts as staff.
     */
    public function isStaff(): bool
    {
        return in_array($this->role, [
            self::ROLE_SUPER_ADMIN,
            self::ROLE_CONTENT_ADMIN,
            self::ROLE_MEMBERSHIP_ADMIN,
            self::ROLE_EDITOR,
            self::ROLE_VIEWER,
        ], true);
    }
}
