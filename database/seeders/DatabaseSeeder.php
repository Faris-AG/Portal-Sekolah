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
        // 4. Create SMK Classes (5 Kelas Fokus Tingkat X via SchoolClassSeeder)
        // ============================================================
        $this->call(SchoolClassSeeder::class);
        $classes = SchoolClass::all();

        // ============================================================
        // 5. Create Students (150 Siswa Proporsional via UserSeeder)
        // ============================================================
        $this->call(UserSeeder::class);

        // ============================================================
        // 6. Generate Schedules (Filter Mapel Produktif via ScheduleSeeder)
        // ============================================================
        $this->call(ScheduleSeeder::class);

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

