<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The bank accounts listed above the donation form.
 *
 * The one table here that Laravel created rather than Prisma: the Next.js app
 * had no bank details to port — /donate hardcoded a footnote saying transfer
 * details were "confirmed by our team after submission" — so there was no
 * schema to inherit and no row to read.
 *
 * Shaped like the three ordered, toggleable list tables beside it (partner,
 * heroslide, testimonial) down to the single createdAt column, so BankAccount
 * looks like Partner to Eloquent and BankAccountSpec looks like PartnerSpec to
 * the CMS.
 *
 * Run it by path:
 *
 *   php artisan migrate --path=database/migrations/2026_09_06_000000_create_bankaccount_table.php
 *
 * A bare `php artisan migrate` would first try to recreate the users table
 * Prisma already wrote, which has never been recorded in a migrations table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bankaccount', function (Blueprint $table) {
            // A cuid from BaseModel::booted(); MySQL has no default for it.
            $table->string('id', 191)->primary();
            $table->string('name', 191);
            $table->string('accountNumber', 191);
            $table->string('accountName', 191)->nullable();
            $table->string('logoUrl', 191)->nullable();
            $table->integer('order')->default(0);
            $table->boolean('active')->default(true);
            $table->dateTime('createdAt', 3)->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bankaccount');
    }
};
