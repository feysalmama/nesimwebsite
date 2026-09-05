<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Replaces next-auth's CredentialsProvider (lib/auth.ts) with a plain Laravel
 * session guard.
 *
 * authorize() in the old provider did nothing beyond "does the email exist and
 * does the password match" — role was never checked at sign-in, only later by
 * requireStaff() on individual admin API routes. That behaviour is preserved so
 * no existing account is locked out by the port.
 */
class LoginController extends Controller
{
    public function show(): View
    {
        return view('admin.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = Str::transliterate(Str::lower($credentials['email']).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Too many login attempts. Please try again in '.RateLimiter::availableIn($throttleKey).' seconds.',
            ]);
        }

        $user = User::where('email', $credentials['email'])->first();

        /*
         * verifyPassword(), never Auth::attempt(): the stored hashes were written
         * by bcryptjs with a $2a$ prefix, which Laravel's BcryptHasher refuses to
         * check and throws on. See the docblock on User::verifyPassword().
         */
        if (! $user || ! $user->verifyPassword($credentials['password'])) {
            RateLimiter::hit($throttleKey);

            // The same message for "no such user" and "wrong password", so the
            // form cannot be used to enumerate which emails have accounts.
            // Literal English on purpose: the Next.js admin panel was never
            // translated, so there is no lang key to reuse.
            throw ValidationException::withMessages([
                'email' => 'Invalid email or password.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        Auth::login($user);

        // Without this the session id used while unauthenticated would carry over
        // into the authenticated session.
        $request->session()->regenerate();

        $this->log($user->id, 'login', 'User', $user->id);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->log($request->user()?->id, 'logout', 'User', $request->user()?->id);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    /**
     * logActivity() from lib/activityLog.ts. Deliberately non-fatal: a failed
     * audit row must never log an editor out or block an action.
     */
    private function log(?string $userId, string $action, string $entity, ?string $entityId = null): void
    {
        try {
            ActivityLog::create([
                'userId' => $userId,
                'action' => $action,
                'entity' => $entity,
                'entityId' => $entityId,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
