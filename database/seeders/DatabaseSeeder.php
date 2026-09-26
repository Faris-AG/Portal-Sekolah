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

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Users
        $admin = User::factory()->create([
            'name' => 'Admin Utama',
            'email' => 'admin@sekolah.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $guru1 = User::factory()->create([
            'name' => 'Budi Santoso, S.Pd',
            'email' => 'guru@sekolah.test',
            'password' => Hash::make('password'),
            'role' => 'guru',
        ]);

        $guru2 = User::factory()->create([
            'name' => 'Siti Aminah, M.Pd',
            'email' => 'siti@sekolah.test',
            'password' => Hash::make('password'),
            'role' => 'guru',
        ]);

        $guru3 = User::factory()->create([
            'name' => 'Rahmat Hidayat, S.Kom',
            'email' => 'rahmat@sekolah.test',
            'password' => Hash::make('password'),
            'role' => 'guru',
        ]);

        // 2. Create Classes
        $class1 = SchoolClass::create([
            'name' => 'X MIPA 1',
            'grade_level' => 10,
            'wali_kelas' => $guru1->name,
        ]);

        $class2 = SchoolClass::create([
            'name' => 'X MIPA 2',
            'grade_level' => 10,
            'wali_kelas' => $guru2->name,
        ]);

        $class3 = SchoolClass::create([
            'name' => 'XI IPS 1',
            'grade_level' => 11,
            'wali_kelas' => $guru3->name,
        ]);

        // 3. Create Subjects
        $subjMath = Subject::create(['name' => 'Matematika Wajib', 'code' => 'MTK-10', 'description' => 'Matematika Wajib Kelas 10']);
        $subjIndo = Subject::create(['name' => 'Bahasa Indonesia', 'code' => 'BIND-10', 'description' => 'Bahasa Indonesia Kelas 10']);
        $subjEng = Subject::create(['name' => 'Bahasa Inggris', 'code' => 'BING-10', 'description' => 'Bahasa Inggris Lintas Minat']);
        $subjFisika = Subject::create(['name' => 'Fisika', 'code' => 'FIS-10', 'description' => 'Fisika Peminatan']);
        $subjSejarah = Subject::create(['name' => 'Sejarah', 'code' => 'SEJ-10', 'description' => 'Sejarah Indonesia']);

        // Assign subjects to teachers
        $guru1->update(['subject_id' => $subjMath->id]);
        $guru2->update(['subject_id' => $subjIndo->id]);
        $guru3->update(['subject_id' => $subjEng->id]);

        // 4. Create Students
        $siswa1 = User::factory()->create([
            'name' => 'Andi Wijaya',
            'email' => 'siswa@sekolah.test',
            'password' => Hash::make('password'),
            'role' => 'siswa',
            'class_id' => $class1->id,
        ]);

        $studentsClass1 = User::factory(2)->create([
            'password' => Hash::make('password'),
            'role' => 'siswa',
            'class_id' => $class1->id,
        ]);

        $studentsClass2 = User::factory(2)->create([
            'password' => Hash::make('password'),
            'role' => 'siswa',
            'class_id' => $class2->id,
        ]);

        $studentsClass3 = User::factory(1)->create([
            'password' => Hash::make('password'),
            'role' => 'siswa',
            'class_id' => $class3->id,
        ]);

        // 5. Create Schedules (for X MIPA 1 and others)
        Schedule::create([
            'school_class_id' => $class1->id,
            'subject_id' => $subjMath->id,
            'teacher_id' => $guru1->id,
            'day' => 'Senin',
            'start_time' => '07:30',
            'end_time' => '09:00',
        ]);
        Schedule::create([
            'school_class_id' => $class1->id,
            'subject_id' => $subjIndo->id,
            'teacher_id' => $guru2->id,
            'day' => 'Senin',
            'start_time' => '09:30',
            'end_time' => '11:00',
        ]);
        Schedule::create([
            'school_class_id' => $class2->id,
            'subject_id' => $subjFisika->id,
            'teacher_id' => $guru1->id,
            'day' => 'Selasa',
            'start_time' => '07:30',
            'end_time' => '09:00',
        ]);
        Schedule::create([
            'school_class_id' => $class1->id,
            'subject_id' => $subjEng->id,
            'teacher_id' => $guru3->id,
            'day' => 'Rabu',
            'start_time' => '08:00',
            'end_time' => '09:30',
        ]);
        Schedule::create([
            'school_class_id' => $class1->id,
            'subject_id' => $subjFisika->id,
            'teacher_id' => $guru1->id,
            'day' => 'Kamis',
            'start_time' => '10:00',
            'end_time' => '11:30',
        ]);
        Schedule::create([
            'school_class_id' => $class1->id,
            'subject_id' => $subjSejarah->id,
            'teacher_id' => $guru2->id,
            'day' => 'Jumat',
            'start_time' => '07:30',
            'end_time' => '09:00',
        ]);

        // 6. Create Assignments
        $assignment1 = Assignment::create([
            'teacher_id' => $guru1->id,
            'school_class_id' => $class1->id,
            'subject_id' => $subjMath->id,
            'title' => 'Tugas Matriks dan Vektor',
            'description' => 'Kerjakan LKS halaman 12-15 bagian A dan B. Upload dalam format PDF.',
            'due_date' => Carbon::now()->addDays(2)->format('Y-m-d 23:59:00'),
        ]);

        $assignment2 = Assignment::create([
            'teacher_id' => $guru2->id,
            'school_class_id' => $class1->id,
            'subject_id' => $subjIndo->id,
            'title' => 'Makalah Teks Eksposisi',
            'description' => 'Buat teks eksposisi bertema Lingkungan Hidup minimal 3 paragraf.',
            'due_date' => Carbon::now()->addDays(1)->format('Y-m-d 23:59:00'),
        ]);

        // 7. Create Submissions
        Submission::create([
            'assignment_id' => $assignment1->id,
            'student_id' => $siswa1->id,
            'file_path' => 'dummy/path.pdf',
            'file_name' => 'Tugas_Matriks_Andi.pdf',
            'note' => 'Maaf pak, tulisan saya agak kurang jelas',
            'submitted_at' => Carbon::now()->subHours(2),
            'grade' => 85,
            'feedback' => 'Sudah cukup baik, pelajari lagi perkalian matriks.',
        ]);

        // 8. Create Attendances (Today and past week)
        $today = Carbon::today();
        $studentsInClass1 = User::where('class_id', $class1->id)->get();
        
        foreach ($studentsInClass1 as $student) {
            // Today's attendance for Math
            Attendance::create([
                'school_class_id' => $class1->id,
                'student_id' => $student->id,
                'teacher_id' => $guru1->id,
                'subject_id' => $subjMath->id,
                'date' => $today->format('Y-m-d'),
                'status' => $student->id === $siswa1->id ? 'hadir' : 'hadir',
            ]);

            // Yesterday's attendance
            Attendance::create([
                'school_class_id' => $class1->id,
                'student_id' => $student->id,
                'teacher_id' => $guru1->id,
                'subject_id' => $subjFisika->id,
                'date' => $today->copy()->subDays(1)->format('Y-m-d'),
                'status' => $student->id === $siswa1->id ? 'hadir' : 'sakit',
                'note' => $student->id === $siswa1->id ? null : 'Demam',
            ]);
        }
    }
}
