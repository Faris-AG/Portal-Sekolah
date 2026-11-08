<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VerifyEmailController extends Controller
{
    /**
     * Mark the user's email address as verified and render Discord-style verification confirmation page.
     */
    public function __invoke(Request $request, $id = null, $hash = null): Response|RedirectResponse
    {
        $id = $id ?? $request->route('id');
        $hash = $hash ?? $request->route('hash');

        $user = User::find($id);

        if (! $user) {
            return redirect()->route('register')->with('error', 'Link verifikasi telah kadaluarsa atau akun telah dihapus. Silakan daftar ulang.');
        }

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'Tautan verifikasi tidak valid atau telah dimodifikasi.');
        }

        if (! $user->hasVerifiedEmail()) {
            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
            }
        }

        return Inertia::render('Auth/VerifiedSuccess', [
            'email' => $user->email,
        ]);
    }
}
