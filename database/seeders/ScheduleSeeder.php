<?php

namespace Database\Seeders;

use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Bersihkan data jadwal lama
        Schedule::query()->delete();

        $classes = SchoolClass::all();
        $teachers = User::where('role', 'guru')->with('subject')->get();

        if ($classes->isEmpty() || $teachers->isEmpty()) {
            return;
        }

        // Mapel Umum / Nasional (Berlaku untuk seluruh jurusan)
        $commonSubjectNames = [
            'Matematika',
            'Bahasa Indonesia',
            'Bahasa Inggris',
            'Pendidikan Agama Islam (PAI)',
            'Pendidikan Jasmani (PJOK)',
            'PKn / PPKn',
            'Informatika',
            'Seni Budaya',
        ];

        // Pemetaan Mapel Produktif Kejuruan KHUSUS per Jurusan SMK
        $productiveSubjectMap = [
            'PPLG'    => ['Dasar Pemrograman (PPLG)', 'Rekayasa Perangkat Lunak (PPLG)'],
            'BCF'     => ['Produksi Konten Digital (BCF)'],
            'Animasi' => ['Teknik Animasi & Multimedia (Animasi)'],
            'TKR'     => ['Teknik Kendaraan Ringan (TKR)'],
            'TPFL'    => ['Teknik Pengelasan & Fabrikasi (TPFL)'],
        ];

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

        // Kelompokkan guru berdasarkan subject_id
        $teachersBySubject = [];
        foreach ($teachers as $teacher) {
            $sid = $teacher->subject_id;
            if ($sid) {
                $teachersBySubject[$sid][] = $teacher;
            }
        }

        $teacherSchedule = [];

        foreach ($classes as $class) {
            // 1. Tentukan mapel produktif sesuai jurusan kelas
            $majorProductive = [];
            foreach ($productiveSubjectMap as $majorKey => $prodSubjects) {
                if (str_contains($class->name, $majorKey)) {
                    $majorProductive = $prodSubjects;
                    break;
                }
            }

            // 2. Filter mapel: HANYA mapel umum + mapel produktif jurusan kelas ini
            $allowedNames = array_merge($commonSubjectNames, $majorProductive);
            $availableSubjects = Subject::whereIn('name', $allowedNames)->get();

            if ($availableSubjects->isEmpty()) {
                continue;
            }

            // Shuffle urutan mapel agar variatif
            $subjectPool = $availableSubjects->shuffle()->values();
            $subjIdx = 0;

            // 3. Generate jadwal untuk setiap hari dan slot waktu
            foreach ($days as $day) {
                $slots = ($day === 'Jumat') ? $timeSlotsFriday : $timeSlotsNormal;

                foreach ($slots as $slot) {
                    if ($subjIdx >= $subjectPool->count()) {
                        $subjIdx = 0;
                    }

                    $subject = $subjectPool[$subjIdx];
                    $availTeachers = $teachersBySubject[$subject->id] ?? [];

                    $selectedTeacher = null;
                    $timeKey = $day . '-' . $slot[0];

                    // Cari guru yang belum bentrok pada jam tersebut
                    foreach ($availTeachers as $candidate) {
                        if (!isset($teacherSchedule[$candidate->id][$timeKey])) {
                            $selectedTeacher = $candidate;
                            $teacherSchedule[$candidate->id][$timeKey] = true;
                            break;
                        }
                    }

                    // Jika bentrok semua atau belum terpilih, gunakan guru pertama yang sesuai
                    if (!$selectedTeacher && !empty($availTeachers)) {
                        $selectedTeacher = $availTeachers[0];
                    } elseif (!$selectedTeacher) {
                        $selectedTeacher = $teachers->first();
                    }

                    Schedule::create([
                        'school_class_id' => $class->id,
                        'subject_id'      => $subject->id,
                        'teacher_id'      => $selectedTeacher->id,
                        'day'             => $day,
                        'start_time'      => $slot[0],
                        'end_time'        => $slot[1],
                    ]);

                    $subjIdx++;
                }
            }
        }
    }
}
