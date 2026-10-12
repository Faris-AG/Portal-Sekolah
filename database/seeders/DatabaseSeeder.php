<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Schedule;
use App\Models\Assignment;
use App\Models\Submission;
use App\Models\Attendance;
use App\Models\ForumTopic;
use App\Models\ForumReply;
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

        // 2. Create Subjects (14 Subjects to have 14 Teachers)
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
            'Informatika' => 'INF',
            'Seni Budaya' => 'SBD',
            'Prakarya' => 'PRK'
        ];

        $subjects = [];
        foreach ($subjectNames as $name => $code) {
            $subjects[$name] = Subject::create([
                'name' => $name,
                'code' => $code . '-' . rand(10, 99),
                'description' => $name . ' Terpadu'
            ]);
        }

        // 3. Create Teachers — exactly one per subject, so 14 teachers
        $teachers = [];
        $emails = [
            'guru.mtk', 'guru.bindo', 'guru.bing', 'guru.fisika', 'guru.kimia',
            'guru.biologi', 'guru.sejarah', 'guru.ekonomi', 'guru.geografi',
            'guru.pjok', 'guru.pai', 'guru.informatika', 'guru.seni', 'guru.prakarya'
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

        // 6. Generate Realistic Schedules — every teacher gets at least 1 slot
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

        // Build a map of subject_id => teachers array for round-robin assignment
        $teachersBySubject = [];
        foreach ($teachers as $teacher) {
            $sid = $teacher->subject_id;
            if (!isset($teachersBySubject[$sid])) {
                $teachersBySubject[$sid] = [];
            }
            $teachersBySubject[$sid][] = $teacher;
        }
        $teacherSubjectRoundRobin = []; // subject_id => pointer index

        // Prevent double-booking a teacher at the same day+time
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
                    $sid = $subject->id;

                    // Round-robin through teachers who teach this subject
                    $availTeachers = $teachersBySubject[$sid] ?? [];
                    $teacher = null;

                    // Try to find a non-conflicting teacher
                    $pointer = $teacherSubjectRoundRobin[$sid] ?? 0;
                    $count = count($availTeachers);
                    for ($try = 0; $try < $count; $try++) {
                        $candidate = $availTeachers[$pointer % $count];
                        $pointer++;
                        $timeKey = $day . '-' . $slot[0];
                        if (!isset($teacherSchedule[$candidate->id][$timeKey])) {
                            $teacher = $candidate;
                            $teacherSchedule[$candidate->id][$timeKey] = true;
                            break;
                        }
                    }
                    $teacherSubjectRoundRobin[$sid] = $pointer;

                    // Fallback: use first teacher even if conflicting
                    if (!$teacher) {
                        $teacher = $availTeachers[0];
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
        
        // 7. Add Dummy Assignments & Submissions
        $this->call(AssignmentSeeder::class);

        $siswaList = User::where('role', 'siswa')->where('class_id', $classes[0]->id)->get();

        // Attendance - 7 days history
        for ($d = 6; $d >= 0; $d--) {
            $date = Carbon::today()->subDays($d)->format('Y-m-d');
            
            foreach ($siswaList as $index => $s) {
                $status = 'hadir';
                $note = null;
                
                // Randomize a few absences
                if ($d == 2 && $index == 1) {
                    $status = 'sakit';
                    $note = 'Demam, surat dokter terlampir';
                } elseif ($d == 5 && $index == 2) {
                    $status = 'izin';
                    $note = 'Acara keluarga';
                }

                Attendance::create([
                    'school_class_id' => $classes[0]->id,
                    'student_id' => $s->id,
                    'teacher_id' => $teachers[0]->id,
                    'subject_id' => $teachers[0]->subject_id,
                    'date' => $date,
                    'status' => $status,
                    'note' => $note,
                ]);
            }
        }

        // 8. Add Dummy Announcements
        $admin = User::where('role', 'admin')->first();
        $announcement1 = \App\Models\Announcement::create([
            'user_id' => $admin->id,
            'title' => 'Jadwal Ujian Tengah Semester (UTS)',
            'content' => 'Diberitahukan kepada seluruh siswa dan guru bahwa Ujian Tengah Semester (UTS) akan dilaksanakan mulai minggu depan. Harap persiapkan diri dan materi dengan baik.',
            'target_role' => 'all',
        ]);
        
        $announcement2 = \App\Models\Announcement::create([
            'user_id' => $admin->id,
            'title' => 'Edaran Libur Nasional',
            'content' => 'Sehubungan dengan hari libur nasional pada hari Jumat ini, maka seluruh kegiatan belajar mengajar diliburkan.',
            'target_role' => 'all',
        ]);

        $announcement3 = \App\Models\Announcement::create([
            'user_id' => $admin->id,
            'title' => 'Pembaruan Tata Tertib Sekolah',
            'content' => 'Mohon diperhatikan pembaruan mengenai tata tertib seragam sekolah yang berlaku mulai bulan depan.',
            'target_role' => 'siswa',
        ]);

        // Seeding pivot for announcements read_at
        $siswa = User::where('email', 'siswa@sekolah.test')->first();
        $siswa->readAnnouncements()->attach($announcement1->id, ['read_at' => Carbon::now()]);
        $teachers[0]->readAnnouncements()->attach($announcement2->id, ['read_at' => Carbon::now()]);

        // 9. Add Dummy Materials for all classes and subjects
        $this->call(MaterialSeeder::class);

        // 10. Add Dummy Forum Topics & Replies for all classes
        $this->call(ForumSeeder::class);
    }
}
