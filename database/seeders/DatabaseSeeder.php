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

        // ============================================================
        // 1. Create Admin
        // ============================================================
        User::factory()->create([
            'name'     => 'Admin Utama',
            'email'    => 'admin@sekolah.test',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        // ============================================================
        // 2. Create Subjects (relevan untuk SMK + normatif/adaptif)
        // ============================================================
        $subjectNames = [
            // Normatif / Adaptif
            'Matematika'                             => 'MTK',
            'Bahasa Indonesia'                       => 'BIND',
            'Bahasa Inggris'                         => 'BING',
            'Pendidikan Agama Islam (PAI)'           => 'PAI',
            'Pendidikan Jasmani (PJOK)'              => 'PJK',
            'PKn / PPKn'                             => 'PKN',
            // Produktif umum lintas jurusan
            'Informatika'                            => 'INF',
            'Seni Budaya'                            => 'SBD',
            // Produktif Jurusan
            'Dasar Pemrograman (PPLG)'               => 'PPLG',
            'Rekayasa Perangkat Lunak (PPLG)'        => 'RPL',
            'Produksi Konten Digital (BCF)'          => 'BCF',
            'Teknik Animasi & Multimedia (Animasi)'  => 'ANI',
            'Teknik Kendaraan Ringan (TKR)'          => 'TKR',
            'Teknik Pengelasan & Fabrikasi (TPFL)'   => 'TPFL',
        ];

        $subjects = [];
        foreach ($subjectNames as $name => $code) {
            $subjects[$name] = Subject::create([
                'name'        => $name,
                'code'        => $code . '-' . rand(10, 99),
                'description' => $name . ' (SMK)',
            ]);
        }

        // ============================================================
        // 3. Create Teachers — one per subject
        // ============================================================
        $teacherEmails = [
            'guru.mtk', 'guru.bindo', 'guru.bing', 'guru.pai', 'guru.pjok', 'guru.pkn',
            'guru.informatika', 'guru.seni',
            'guru.pplg1', 'guru.rpl', 'guru.bcf', 'guru.animasi', 'guru.tkr', 'guru.tpfl',
        ];

        $teachers = [];
        $i = 0;
        foreach ($subjects as $subjectName => $subject) {
            $teachers[] = User::factory()->create([
                'name'       => $faker->name . ', S.Pd',
                'email'      => $teacherEmails[$i] . '@sekolah.test',
                'password'   => Hash::make('password'),
                'role'       => 'guru',
                'subject_id' => $subject->id,
            ]);
            $i++;
        }

        // Set demo teacher
        $teachers[0]->update(['email' => 'guru@sekolah.test']);

        // ============================================================
        // 4. Create SMK Classes
        // Jurusan & rombel:
        //   PPLG   : 3 rombel per tingkat
        //   BCF    : 2 rombel per tingkat
        //   Animasi: 2 rombel per tingkat
        //   TKR    : 3 rombel per tingkat
        //   TPFL   : 2 rombel per tingkat
        // ============================================================
        $jurusanRombel = [
            'PPLG'    => 3,
            'BCF'     => 2,
            'Animasi' => 2,
            'TKR'     => 3,
            'TPFL'    => 2,
        ];

        $tingkats = [
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ];

        $classes      = [];
        $teacherIndex = 0;
        $totalTeachers = count($teachers);

        foreach ($tingkats as $level => $prefix) {
            foreach ($jurusanRombel as $jurusan => $rombelCount) {
                for ($r = 1; $r <= $rombelCount; $r++) {
                    $className  = "{$prefix} {$jurusan} {$r}";
                    $waliKelas  = $teachers[$teacherIndex % $totalTeachers];
                    $classes[]  = SchoolClass::create([
                        'name'        => $className,
                        'grade_level' => $level,
                        'teacher_id'  => $waliKelas->id,
                        'wali_kelas'  => $waliKelas->name,
                    ]);
                    $teacherIndex++;
                }
            }
        }

        // ============================================================
        // 5. Create Students (30 per class)
        //    Siswa demo 'siswa@sekolah.test' ditempatkan di kelas pertama (X PPLG 1)
        // ============================================================
        $studentCounter = 1;
        foreach ($classes as $classIndex => $class) {
            for ($s = 1; $s <= 30; $s++) {
                $email = 'siswa' . $studentCounter . '@sekolah.test';
                if ($classIndex === 0 && $s === 1) {
                    $email = 'siswa@sekolah.test';
                    $name  = 'Andi Utama';
                } else {
                    $name = $faker->name;
                }

                User::factory()->create([
                    'name'     => $name,
                    'email'    => $email,
                    'password' => Hash::make('password'),
                    'role'     => 'siswa',
                    'class_id' => $class->id,
                ]);
                $studentCounter++;
            }
        }

        // ============================================================
        // 6. Generate Schedules — every class gets a full timetable
        // ============================================================
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

        $teachersBySubject = [];
        foreach ($teachers as $teacher) {
            $sid = $teacher->subject_id;
            if (!isset($teachersBySubject[$sid])) {
                $teachersBySubject[$sid] = [];
            }
            $teachersBySubject[$sid][] = $teacher;
        }
        $teacherSubjectRoundRobin = [];
        $teacherSchedule          = [];

        foreach ($classes as $class) {
            $subjectsList = collect($subjects)->values()->shuffle();
            $subjIdx = 0;

            foreach ($days as $day) {
                $slots = ($day === 'Jumat') ? $timeSlotsFriday : $timeSlotsNormal;

                foreach ($slots as $slot) {
                    if ($subjIdx >= $subjectsList->count()) {
                        $subjIdx = 0;
                    }

                    $subject = $subjectsList[$subjIdx];
                    $sid     = $subject->id;

                    $availTeachers = $teachersBySubject[$sid] ?? [];
                    $teacher       = null;

                    $pointer = $teacherSubjectRoundRobin[$sid] ?? 0;
                    $count   = count($availTeachers);
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

                    if (!$teacher && count($availTeachers) > 0) {
                        $teacher = $availTeachers[0];
                    } elseif (!$teacher) {
                        $teacher = $teachers[0];
                    }

                    Schedule::create([
                        'school_class_id' => $class->id,
                        'subject_id'      => $subject->id,
                        'teacher_id'      => $teacher->id,
                        'day'             => $day,
                        'start_time'      => $slot[0],
                        'end_time'        => $slot[1],
                    ]);

                    $subjIdx++;
                }
            }
        }

        // ============================================================
        // 7. Dummy Assignments & Submissions
        // ============================================================
        $this->call(AssignmentSeeder::class);

        // ============================================================
        // 8. Attendance — 7 hari terakhir untuk kelas X PPLG 1
        // ============================================================
        $firstClass  = $classes[0]; // X PPLG 1
        $siswaList   = User::where('role', 'siswa')->where('class_id', $firstClass->id)->get();
        $firstTeacher = $teachers[0];

        for ($d = 6; $d >= 0; $d--) {
            $date = Carbon::today()->subDays($d)->format('Y-m-d');

            foreach ($siswaList as $index => $s) {
                $status = 'hadir';
                $note   = null;

                if ($d == 2 && $index == 1) {
                    $status = 'sakit';
                    $note   = 'Demam, surat dokter terlampir';
                } elseif ($d == 5 && $index == 2) {
                    $status = 'izin';
                    $note   = 'Acara keluarga';
                } elseif ($d == 1 && $index == 3) {
                    $status = 'alpa';
                    $note   = null;
                }

                Attendance::create([
                    'school_class_id' => $firstClass->id,
                    'student_id'      => $s->id,
                    'teacher_id'      => $firstTeacher->id,
                    'subject_id'      => $firstTeacher->subject_id,
                    'date'            => $date,
                    'status'          => $status,
                    'note'            => $note,
                ]);
            }
        }

        // ============================================================
        // 9. Dummy Announcements
        // ============================================================
        $admin = User::where('role', 'admin')->first();

        $announcement1 = \App\Models\Announcement::create([
            'user_id'     => $admin->id,
            'title'       => 'Jadwal Ujian Tengah Semester (UTS) Ganjil 2026',
            'content'     => 'Diberitahukan kepada seluruh siswa dan guru SMKN 1 Ciomas bahwa Ujian Tengah Semester (UTS) akan dilaksanakan mulai minggu depan. Harap persiapkan diri dan materi kompetensi kejuruan dengan baik.',
            'target_role' => 'all',
        ]);

        $announcement2 = \App\Models\Announcement::create([
            'user_id'     => $admin->id,
            'title'       => 'Edaran Libur Nasional & Hari Efektif Sekolah',
            'content'     => 'Sehubungan dengan hari libur nasional pada hari Jumat ini, maka seluruh kegiatan belajar mengajar di SKANIC diliburkan. Kegiatan praktik industri dan PKL tetap berjalan sesuai kesepakatan DU/DI.',
            'target_role' => 'all',
        ]);

        $announcement3 = \App\Models\Announcement::create([
            'user_id'     => $admin->id,
            'title'       => 'Pembaruan Tata Tertib Seragam & Penampilan Siswa SKANIC',
            'content'     => 'Mohon diperhatikan pembaruan mengenai tata tertib seragam sekolah dan penampilan siswa SMKN 1 Ciomas yang berlaku mulai bulan depan. Siswa jurusan TPFL dan TKR wajib menggunakan wearpack saat praktik.',
            'target_role' => 'siswa',
        ]);

        $siswaDemo = User::where('email', 'siswa@sekolah.test')->first();
        if ($siswaDemo) {
            $siswaDemo->readAnnouncements()->attach($announcement1->id, ['read_at' => Carbon::now()]);
        }
        $teachers[0]->readAnnouncements()->attach($announcement2->id, ['read_at' => Carbon::now()]);

        // ============================================================
        // 10. Dummy Materials & Forum
        // ============================================================
        $this->call(MaterialSeeder::class);
        $this->call(ForumSeeder::class);
    }
}

