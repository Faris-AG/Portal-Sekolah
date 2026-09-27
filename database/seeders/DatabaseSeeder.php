<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Schedule;
use App\Models\Assignment;
use App\Models\Submission;
use App\Models\Attendance;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // 1. Create Admin
        User::factory()->create([
            'name' => 'Admin Utama',
            'email' => 'admin@sekolah.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // 2. Create Subjects
        $subjectNames = [
            'Matematika Wajib' => 'MTK',
            'Bahasa Indonesia' => 'BIND',
            'Bahasa Inggris' => 'BING',
            'Fisika' => 'FIS',
            'Kimia' => 'KIM',
            'Biologi' => 'BIO',
            'Sejarah' => 'SEJ',
            'Ekonomi' => 'EKO',
            'Geografi' => 'GEO',
            'Pendidikan Jasmani (PJOK)' => 'PJK',
            'Pendidikan Agama Islam (PAI)' => 'PAI',
            'Informatika' => 'INF'
        ];

        $subjects = [];
        foreach ($subjectNames as $name => $code) {
            $subjects[$name] = Subject::create([
                'name' => $name,
                'code' => $code . '-' . rand(10, 99),
                'description' => $name . ' Terpadu'
            ]);
        }

        // 3. Create Teachers
        $teachers = [];
        $emails = [
            'guru.mtk', 'guru.bindo', 'guru.bing', 'guru.fisika', 'guru.kimia',
            'guru.biologi', 'guru.sejarah', 'guru.ekonomi', 'guru.geografi',
            'guru.pjok', 'guru.pai', 'guru.informatika'
        ];
        
        $i = 0;
        foreach ($subjects as $name => $subject) {
            $teachers[] = User::factory()->create([
                'name' => $faker->name . ', S.Pd',
                'email' => $emails[$i] . '@sekolah.test',
                'password' => Hash::make('password'),
                'role' => 'guru',
                'subject_id' => $subject->id,
            ]);
            $i++;
        }
        
        // Add a couple more teachers for variety
        $teachers[] = User::factory()->create([
            'name' => $faker->name . ', M.Pd',
            'email' => 'guru.mtk2@sekolah.test',
            'password' => Hash::make('password'),
            'role' => 'guru',
            'subject_id' => $subjects['Matematika Wajib']->id,
        ]);
        $teachers[] = User::factory()->create([
            'name' => $faker->name . ', S.Pd',
            'email' => 'guru.bing2@sekolah.test',
            'password' => Hash::make('password'),
            'role' => 'guru',
            'subject_id' => $subjects['Bahasa Inggris']->id,
        ]);

        // 4. Create Classes
        $classNames = ['X MIPA 1', 'X MIPA 2', 'X IPS 1', 'XI MIPA 1', 'XI MIPA 2', 'XII MIPA 1'];
        $classes = [];
        
        foreach ($classNames as $index => $cName) {
            $level = explode(' ', $cName)[0] === 'X' ? 10 : (explode(' ', $cName)[0] === 'XI' ? 11 : 12);
            $classes[] = SchoolClass::create([
                'name' => $cName,
                'grade_level' => $level,
                'teacher_id' => $teachers[$index]->id, // First 6 teachers are wali kelas
                'wali_kelas' => $teachers[$index]->name,
            ]);
        }

        // 5. Create Students (30 per class)
        $studentCounter = 1;
        foreach ($classes as $index => $class) {
            for ($s = 1; $s <= 30; $s++) {
                $email = 'siswa' . $studentCounter . '@sekolah.test';
                // Make sure 'siswa@sekolah.test' exists at class 1
                if ($index === 0 && $s === 1) {
                    $email = 'siswa@sekolah.test';
                    $name = 'Andi Utama';
                } else {
                    $name = $faker->name;
                }

                User::factory()->create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make('password'),
                    'role' => 'siswa',
                    'class_id' => $class->id,
                ]);
                $studentCounter++;
            }
        }

        // 6. Generate Realistic Schedules (No Conflicts)
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $timeSlotsNormal = [
            ['06:30', '08:00'],
            ['08:00', '09:30'],
            ['10:00', '11:30'],
            ['12:30', '13:45'],
            ['13:45', '15:00'],
        ];
        $timeSlotsFriday = [
            ['07:30', '09:00'],
            ['09:15', '10:45'],
        ];

        // Ensure teacher doesn't have multiple classes at the same time
        $teacherSchedule = [];

        foreach ($classes as $class) {
            $subjectsList = collect($subjects)->values()->shuffle();
            $subjIdx = 0;

            foreach ($days as $day) {
                $slots = ($day === 'Jumat') ? $timeSlotsFriday : $timeSlotsNormal;
                
                foreach ($slots as $slotIndex => $slot) {
                    if ($subjIdx >= $subjectsList->count()) {
                        $subjIdx = 0; // repeat subjects
                    }
                    
                    $subject = $subjectsList[$subjIdx];
                    // Find teacher for this subject
                    $availTeachers = collect($teachers)->where('subject_id', $subject->id)->values();
                    $teacher = $availTeachers->first(); // fallback
                    
                    // Try to find a non-conflicting teacher
                    foreach ($availTeachers as $t) {
                        $timeKey = $day . '-' . $slot[0];
                        if (!isset($teacherSchedule[$t->id][$timeKey])) {
                            $teacher = $t;
                            $teacherSchedule[$t->id][$timeKey] = true;
                            break;
                        }
                    }

                    Schedule::create([
                        'school_class_id' => $class->id,
                        'subject_id' => $subject->id,
                        'teacher_id' => $teacher->id,
                        'day' => $day,
                        'start_time' => $slot[0],
                        'end_time' => $slot[1],
                    ]);

                    $subjIdx++;
                }
            }
        }
        
        // 7. Add Some Dummy Assignments & Submissions
        $assignment = Assignment::create([
            'teacher_id' => $teachers[0]->id,
            'school_class_id' => $classes[0]->id,
            'subject_id' => $teachers[0]->subject_id,
            'title' => 'Tugas ' . $teachers[0]->subject->name,
            'description' => 'Kerjakan LKS halaman 12-15 bagian A dan B.',
            'due_date' => Carbon::now()->addDays(2)->format('Y-m-d 23:59:00'),
        ]);

        $siswa = User::where('email', 'siswa@sekolah.test')->first();
        Submission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $siswa->id,
            'file_path' => 'dummy/path.pdf',
            'file_name' => 'Tugas_Andi.pdf',
            'note' => 'Ini tugas saya pak',
            'submitted_at' => Carbon::now()->subHours(2),
            'grade' => 85,
            'feedback' => 'Sudah cukup baik.',
        ]);

        // Attendance
        Attendance::create([
            'school_class_id' => $classes[0]->id,
            'student_id' => $siswa->id,
            'teacher_id' => $teachers[0]->id,
            'subject_id' => $teachers[0]->subject_id,
            'date' => Carbon::today()->format('Y-m-d'),
            'status' => 'hadir',
        ]);
    }
}
