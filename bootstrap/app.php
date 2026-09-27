<?php

use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            // Applied to the /{locale} site group only, never globally.
            'locale' => SetLocale::class,
        ]);

        /*
         * The CMS guard is not named 'login', so Laravel's defaults would send a
         * logged-out editor to '/' and a logged-in one back to the public site.
         * Both are pointed at the admin panel explicitly.
         */
        $middleware->redirectGuestsTo(fn () => route('admin.login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (PostTooLargeException $exception, Request $request): ?JsonResponse {
            if ($request->is('admin/upload')) {
                return response()->json([
                    'error' => 'Upload exceeds the server request limit of '.ini_get('post_max_size').'. Please choose a smaller file.',
                ], 413);
            }

            return null;
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
