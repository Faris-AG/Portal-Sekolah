<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Kustomisasi Email Verifikasi Bahasa Indonesia
        VerifyEmail::toMailUsing(function ($notifiable, string $url) {
            return (new MailMessage)
                ->subject('Verifikasi Alamat Email - Portal Akademik')
                ->greeting('Halo, ' . ($notifiable->name ?? 'Pengguna') . '!')
                ->line('Terima kasih telah mendaftar di Portal Akademik SMKN 1 Ciomas. Akun Anda telah berhasil didaftarkan, silakan klik tombol di bawah ini untuk memverifikasi alamat email Anda:')
                ->action('Verifikasi Alamat Email', $url)
                ->line('Tautan verifikasi email ini akan kadaluarsa dalam 15 menit.')
                ->line('Jika Anda tidak merasa mendaftar atau membuat akun di Portal Akademik, silakan abaikan email ini.')
                ->salutation("Salam hangat,\nTim Portal Akademik SMKN 1 Ciomas");
        });

        // Kustomisasi Email Reset Password Bahasa Indonesia
        ResetPassword::toMailUsing(function ($notifiable, string $token) {
            $resetUrl = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            $expire = config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60);

            return (new MailMessage)
                ->subject('Atur Ulang Kata Sandi - Portal Akademik')
                ->greeting('Halo, ' . ($notifiable->name ?? 'Pengguna') . '!')
                ->line('Kami menerima permintaan untuk mengatur ulang kata sandi akun Portal Akademik Anda.')
                ->action('Atur Ulang Kata Sandi', $resetUrl)
                ->line('Tautan pengaturan ulang kata sandi ini akan kedaluwarsa dalam ' . $expire . ' menit.')
                ->line('Jika Anda tidak meminta pengaturan ulang kata sandi, tidak ada tindakan lebih lanjut yang diperlukan dan akun Anda tetap aman.')
                ->salutation("Salam hangat,\nTim Portal Akademik SMKN 1 Ciomas");
        });
    }
}
