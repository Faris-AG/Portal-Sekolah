<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $defaultPassword = Hash::make('password');

        // Pastikan kelas SMK sudah tersedia
        $classes = SchoolClass::all();
        if ($classes->isEmpty()) {
            $this->call(SchoolClassSeeder::class);
            $classes = SchoolClass::all();
        }

        // ============================================================
        // 1. Akun Siswa Demo (Andi Utama) di kelas "X PPLG 1"
        // ============================================================
        $demoClass = SchoolClass::where('name', 'X PPLG 1')->first() ?? $classes->first();

        User::updateOrCreate(
            ['email' => 'siswa@sekolah.test'],
            [
                'name'     => 'Andi Utama',
                'password' => $defaultPassword,
                'role'     => 'siswa',
                'class_id' => $demoClass->id,
            ]
        );

        // ============================================================
        // 2. Buat Dummy Siswa Hingga Total Tepat 150 Siswa
        //    Didistribusikan ke 5 kelas SMK secara acak (inRandomOrder)
        //    sehingga rata-rata setiap kelas terisi ~30 siswa
        // ============================================================
        for ($s = 2; $s <= 150; $s++) {
            $randomClass = SchoolClass::inRandomOrder()->first() ?? $demoClass;

            User::updateOrCreate(
                ['email' => 'siswa' . $s . '@sekolah.test'],
                [
                    'name'     => $faker->name,
                    'password' => $defaultPassword,
                    'role'     => 'siswa',
                    'class_id' => $randomClass->id,
                ]
            );
        }

        // ============================================================
        // 3. Validasi: Pastikan TIDAK ADA siswa yang berada di luar 5 kelas SMK
        // ============================================================
        $validClassIds = $classes->pluck('id')->toArray();
        $staleStudents = User::where('role', 'siswa')
            ->where(function ($q) use ($validClassIds) {
                $q->whereNull('class_id')
                  ->orWhereNotIn('class_id', $validClassIds);
            })->get();

        foreach ($staleStudents as $stale) {
            $stale->update([
                'class_id' => SchoolClass::inRandomOrder()->first()->id,
            ]);
        }
    }
}
