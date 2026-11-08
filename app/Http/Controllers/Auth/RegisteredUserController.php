<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\OfficialEmailDomain;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        // Bersihkan otomatis akun yang belum verifikasi email dan sudah lewat dari 15 menit
        $this->pruneUnverifiedAccounts();

        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Bersihkan otomatis akun gantung yang sudah lewat dari 15 menit
        $this->pruneUnverifiedAccounts();

        // Sebelum validasi unique, jika email ini pernah didaftarkan tetapi belum diverifikasi,
        // hapus data akun gantung tersebut agar pendaftar bisa langsung mendaftar ulang tanpa tertahan error "Email sudah terdaftar".
        if ($request->filled('email')) {
            User::where('email', $request->email)
                ->whereNull('email_verified_at')
                ->delete();
        }

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:' . User::class,
                new OfficialEmailDomain(),
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Optimasi: kirim email verifikasi di latar belakang setelah response dikirim agar registrasi instan tanpa delay SMTP
        defer(fn () => event(new Registered($user)));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }

    /**
     * Bersihkan akun yang belum verifikasi email dan sudah lebih dari 15 menit.
     */
    private function pruneUnverifiedAccounts(): void
    {
        User::whereNull('email_verified_at')
            ->where('updated_at', '<', now()->subMinutes(15))
            ->delete();
    }
}
