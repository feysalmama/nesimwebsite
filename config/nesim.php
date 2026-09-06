<?php

/*
 * Settings that belong to this application rather than to Laravel.
 *
 * They are read through config() at the point of use and never through env(),
 * because production runs `php artisan config:cache`. LoadEnvironmentVariables
 * returns before .env is opened once the config is cached, so an env() call
 * outside a config file quietly yields its default on the server while still
 * working on a developer's machine. A seeder that read env('ADMIN_PASSWORD')
 * directly would generate a different password in production than the one
 * written in .env, and nothing would say so.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Bootstrap super admin
    |--------------------------------------------------------------------------
    |
    | Read by Database\Seeders\SuperAdminSeeder, which creates this account
    | only when no row holds the address. It never updates an existing one, so
    | re-running `php artisan db:seed` against a live database cannot change
    | anyone's password or role and cannot lock anybody out.
    |
    | ADMIN_PASSWORD is meant to be left unset. The seeder then generates a
    | random one and prints it once, which is the safe shape for a first
    | deploy: a default written here would be a credential published to
    | everyone who can read the repository. Set it only where a deploy has to
    | be unattended, and rotate it afterwards.
    |
    */

    'admin' => [
        'name' => env('ADMIN_NAME', 'Nesim Admin'),
        'email' => env('ADMIN_EMAIL', 'admin@nesim.org'),
        'password' => env('ADMIN_PASSWORD'),
    ],

];
