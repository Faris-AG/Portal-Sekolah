<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Seeder;

class SchoolClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Daftar kelas SMK tingkat X dan XI (XI kosong, siap sebagai kelas tujuan kenaikan)
        $classes = [
            ['name' => 'X PPLG 1',    'grade_level' => 10],
            ['name' => 'X BCF 1',     'grade_level' => 10],
            ['name' => 'X Animasi 1', 'grade_level' => 10],
            ['name' => 'X TKR 1',     'grade_level' => 10],
            ['name' => 'X TPFL 1',    'grade_level' => 10],
            // Kelas XI – kosong, siap sebagai tujuan kenaikan kelas dari X
            ['name' => 'XI PPLG 1',   'grade_level' => 11],
            ['name' => 'XI BCF 1',    'grade_level' => 11],
        ];

        // 2. Hapus data kelas lama atau kelas di luar 5 kelas fokus ini
        $allowedNames = array_column($classes, 'name');
        SchoolClass::whereNotIn('name', $allowedNames)->delete();

        // 3. Ambil guru yang tersedia untuk penugasan wali kelas
        $teachers = User::where('role', 'guru')->get();
        $totalTeachers = $teachers->count();

        // 4. Generate/perbarui 5 kelas SMK
        foreach ($classes as $index => $classData) {
            $waliKelas = $totalTeachers > 0 ? $teachers[$index % $totalTeachers] : null;

            SchoolClass::updateOrCreate(
                ['name' => $classData['name']],
                [
                    'grade_level' => $classData['grade_level'],
                    'teacher_id'  => $waliKelas ? $waliKelas->id : null,
                    'wali_kelas'  => $waliKelas ? $waliKelas->name : null,
                ]
            );
        }
    }
}
