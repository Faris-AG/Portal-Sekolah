<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Submission;
use App\Models\User;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ambil seluruh guru
        $teachers = User::whereIn('role', ['guru', 'teacher'])->with('subject')->get();

        if ($teachers->isEmpty()) {
            if ($this->command) {
                $this->command->warn('Tidak ada data guru. Pastikan seeder guru/user telah dijalankan.');
            }
            return;
        }

        // Hapus data submissions & assignments lama untuk memastikan idempoten dan mencegah duplikasi
        Submission::query()->delete();
        Assignment::query()->delete();

        // 2. Kamus materi tugas realistis per mata pelajaran
        $curatedAssignments = [
            'Informatika' => [
                'active' => [
                    'title' => 'Praktikum Pemrograman Dasar: Algoritma & Percabangan',
                    'description' => 'Buatlah program sederhana dengan Python yang mengimplementasikan percabangan if-elif-else dan perulangan loop. Lampirkan source code (.py) atau screenshot eksekusi dalam format PDF.',
                ],
                'past' => [
                    'title' => 'Tugas Analisis Struktur Data & Desain Algoritma',
                    'description' => 'Kerjakan studi kasus perancangan flowchart dan pseudocode untuk sistem pencarian data array. Tuliskan jawaban secara rapi dalam format PDF.',
                ],
            ],
            'Matematika Wajib' => [
                'active' => [
                    'title' => 'Latihan Soal Matriks & Sistem Persamaan',
                    'description' => 'Selesaikan 5 butir soal pemecahan masalah matriks, determinan, dan invers pada buku paket halaman 45 bagian B. Tuliskan langkah pengerjaan dengan jelas.',
                ],
                'past' => [
                    'title' => 'Tugas Mandiri: SPLTV & Metode Determinan',
                    'description' => 'Kerjakan latihan soal SPLTV menggunakan metode gabungan eliminasi-substitusi dan metode cramer. Kumpulkan sebelum batas waktu.',
                ],
            ],
            'Bahasa Indonesia' => [
                'active' => [
                    'title' => 'Penulisan Teks Anekdot & Kritik Sosial',
                    'description' => 'Susunlah sebuah teks anekdot bertema fenomena sosial atau pendidikan terkini dengan memperhatikan kaidah kebahasaan dan struktur teks (abstraksi, orientasi, krisis, reaksi, koda).',
                ],
                'past' => [
                    'title' => 'Analisis Struktur dan Kaidah Kebahasaan Teks Eksposisi',
                    'description' => 'Bacalah artikel opini yang disediakan, kemudian tentukan tesis, argumen pendukung, dan rekomendasi serta analisis konjungsi yang digunakan.',
                ],
            ],
            'Bahasa Inggris' => [
                'active' => [
                    'title' => 'Analytical Exposition Essay: The Importance of Digital Literacy',
                    'description' => 'Write an analytical exposition essay consisting of 300-400 words about why digital literacy is essential for modern students. Ensure your essay includes a thesis, arguments, and reiteration.',
                ],
                'past' => [
                    'title' => 'Reading Comprehension & Grammar Exercise: Cause and Effect',
                    'description' => 'Complete the reading comprehension questions and cause-effect sentence transformation worksheet on pages 28-30.',
                ],
            ],
            'Fisika' => [
                'active' => [
                    'title' => 'Laporan Praktikum Kinematika & Gerak Parabola',
                    'description' => 'Susunlah laporan praktikum gerak parabola menggunakan simulator PhET. Cantumkan tabel data pengamatan, grafik hubungan sudut elevasi terhadap jarak maksimum, dan analisis kesimpulan.',
                ],
                'past' => [
                    'title' => 'Latihan Soal Penerapan Hukum Newton I, II, dan III',
                    'description' => 'Kerjakan 6 soal analisis gaya gesek, tegangan tali, dan percepatan benda pada bidang miring. Lengkapi dengan diagram benda bebas (free-body diagram).',
                ],
            ],
            'Kimia' => [
                'active' => [
                    'title' => 'Perhitungan Konsep Mol dan Stoikiometri Reaksi',
                    'description' => 'Selesaikan lembar kerja stoikiometri reaksi pembakaran dan penentuan pereaksi pembatas. Perhatikan kesetaraan koefisien reaksi dan massa molar unsur.',
                ],
                'past' => [
                    'title' => 'Tugas Ringkasan Sistem Periodik Unsur & Ikatan Kimia',
                    'description' => 'Buatlah infografis atau rangkuman digital mengenai kecenderungan sifat keperiodikan unsur (jari-jari atom, energi ionisasi, afinitas elektron, dan elektronegativitas).',
                ],
            ],
            'Biologi' => [
                'active' => [
                    'title' => 'Laporan Pengamatan Struktur Sel Hewan dan Tumbuhan',
                    'description' => 'Berdasarkan hasil pengamatan mikroskop kemarin, buat laporan perbandingan organel sel hewan dan tumbuhan disertai gambar sketsa dan fungsinya.',
                ],
                'past' => [
                    'title' => 'Analisis Mekanisme Enzim dan Katabolisme Karbohidrat',
                    'description' => 'Jawablah pertanyaan analisis grafik pengaruh pH dan suhu terhadap laju reaksi enzimatis serta tahap-tahap respirasi aerob.',
                ],
            ],
            'Sejarah' => [
                'active' => [
                    'title' => 'Esai Analisis Tokoh dan Pergerakan Nasional Indonesia',
                    'description' => 'Pilihlah salah satu organisasi pergerakan nasional (Budi Utomo, Sarekat Islam, atau Indische Partij) dan tulis esai kritis mengenai kontribusinya terhadap kebangkitan nasional.',
                ],
                'past' => [
                    'title' => 'Resume Peristiwa Sekitar Proklamasi Kemerdekaan 1945',
                    'description' => 'Buat kronologi runtut peristiwa Rengasdengklok hingga pembacaan teks Proklamasi beserta makna historisnya.',
                ],
            ],
            'Ekonomi' => [
                'active' => [
                    'title' => 'Analisis Keseimbangan Pasar & Elastisitas Permintaan',
                    'description' => 'Hitung fungsi permintaan, penawaran, dan titik keseimbangan pasar (ekuilibrium) dari tabel harga barang pokok serta tentukan koefisien elastisitasnya.',
                ],
                'past' => [
                    'title' => 'Tugas Pengelolaan APBN dan APBD dalam Pembangunan',
                    'description' => 'Analisis komponen penerimaan dan belanja negara tahun berjalan dan jelaskan peran kebijakan fiskal dalam menjaga stabilitas ekonomi.',
                ],
            ],
            'Geografi' => [
                'active' => [
                    'title' => 'Analisis Peta Topografi dan Penginderaan Jauh',
                    'description' => 'Interpretasikan citra satelit dan hitung kontur interval serta kemiringan lereng pada peta topografi terlampir.',
                ],
                'past' => [
                    'title' => 'Studi Kasus Mitigasi Bencana Gempa Bumi dan Vulkanisme',
                    'description' => 'Susunlah rencana aksi mitigasi bencana berbasis kearifan lokal di wilayah rawan gempa dan gunung berapi.',
                ],
            ],
            'Pendidikan Jasmani (PJOK)' => [
                'active' => [
                    'title' => 'Penyusunan Program Latihan Kebugaran Jasmani Mandiri',
                    'description' => 'Rancang program latihan sirkuit mingguan untuk melatih daya tahan kardiovaskular dan kekuatan otot inti tubuh. Lengkapi dengan jadwal dan intensitas latihan.',
                ],
                'past' => [
                    'title' => 'Analisis Taktik dan Peraturan Permainan Bola Voli',
                    'description' => 'Tuliskan rangkuman pola penyerangan (smash) dan pertahanan (block) dalam permainan bola voli modern sesuai standar FIVB.',
                ],
            ],
            'Pendidikan Agama Islam (PAI)' => [
                'active' => [
                    'title' => 'Kajian Fiqih Muamalah: Transaksi Ekonomi Syariah Modern',
                    'description' => 'Tuliskan resume tentang akad jual beli online, fintech syariah, dan prinsip menghindari maisir, gharar, serta riba dalam transaksi modern.',
                ],
                'past' => [
                    'title' => 'Resume Akhlak Terpuji: Perilaku Jujur, Amanah, dan Istiqomah',
                    'description' => 'Buat refleksi diri mengenai implementasi sifat jujur dan tanggung jawab dalam kehidupan bermasyarakat dan lingkungan sekolah.',
                ],
            ],
            'Seni Budaya' => [
                'active' => [
                    'title' => 'Perancangan Karya Seni Rupa Dua Dimensi Ragam Hias',
                    'description' => 'Buatlah sketsa desain motif batik atau ornamen nusantara dengan teknik stilasi flora/fauna pada kertas gambar A3. Foto hasil sketsa dan kumpulkan dalam format PDF.',
                ],
                'past' => [
                    'title' => 'Apresiasi Musik Tradisional Nusantara',
                    'description' => 'Tonton video pertunjukan musik tradisional nusantara dan buat ulasan apresiasi mengenai fungsi musik, instrumen yang digunakan, dan nilai estetikanya.',
                ],
            ],
            'Prakarya' => [
                'active' => [
                    'title' => 'Proposal Perencanaan Usaha Pengolahan Makanan Khas Daerah',
                    'description' => 'Susun proposal usaha modifikasi makanan khas daerah yang mencakup analisis SWOT, kebutuhan bahan baku, perhitungan HPP (Harga Pokok Produksi), dan strategi pemasaran.',
                ],
                'past' => [
                    'title' => 'Laporan Pembuatan Kerajinan Berbahan Keras Alami',
                    'description' => 'Dokumentasikan tahapan pembuatan produk kerajinan dari bambu/kayu beserta perhitungan biaya dan dokumentasi foto proses pembuatan.',
                ],
            ],
        ];

        $totalAssignmentsCreated = 0;
        $totalSubmissionsCreated = 0;

        foreach ($teachers as $teacher) {
            // Tentukan mata pelajaran guru
            $subject = $teacher->subject;
            if (!$subject && $teacher->subject_id) {
                $subject = Subject::find($teacher->subject_id);
            }
            if (!$subject) {
                $firstSchedule = Schedule::where('teacher_id', $teacher->id)->with('subject')->first();
                $subject = $firstSchedule?->subject ?? Subject::first();
            }

            $subjectName = $subject?->name ?? 'Mata Pelajaran';
            $subjectSlug = Str::slug($subjectName, '_');

            // Ambil kelas yang diajar guru bersangkutan
            $schedules = Schedule::where('teacher_id', $teacher->id)->with('schoolClass')->get();
            $classesTaught = $schedules->pluck('schoolClass')->filter()->unique('id')->values();

            if ($classesTaught->isEmpty() && $teacher->waliClass) {
                $classesTaught = collect([$teacher->waliClass]);
            }
            if ($classesTaught->isEmpty()) {
                $classesTaught = SchoolClass::all();
            }

            $classActive = $classesTaught->first();
            $classPast = $classesTaught->count() > 1 ? $classesTaught[1] : $classActive;

            // Konten tugas
            $config = $curatedAssignments[$subjectName] ?? [
                'active' => [
                    'title' => "Tugas Mandiri Pembelajaran: {$subjectName}",
                    'description' => "Pelajari materi modul Bab terbaru dan selesaikan lembar evaluasi pemahaman. Unggah hasil pengerjaan tepat waktu dalam format dokumen/PDF.",
                ],
                'past' => [
                    'title' => "Rangkuman Materi & Evaluasi Pembelajaran: {$subjectName}",
                    'description' => "Buat rangkuman materi pokok bahasan sebelumnya dan kerjakan latihan soal mandiri.",
                ],
            ];

            // ----------------------------------------------------
            // 1. TUGAS AKTIF (Deadline 3-5 hari ke depan)
            // ----------------------------------------------------
            $activeCreated = Carbon::now()->subDays(rand(1, 3))->subHours(rand(1, 8));
            $activeDueDate = Carbon::now()->addDays(rand(3, 5))->setTime(23, 59, 0);

            $assignmentActive = Assignment::create([
                'teacher_id' => $teacher->id,
                'school_class_id' => $classActive->id,
                'subject_id' => $subject->id,
                'title' => $config['active']['title'],
                'description' => $config['active']['description'],
                'due_date' => $activeDueDate,
                'created_at' => $activeCreated,
                'updated_at' => $activeCreated,
            ]);
            $totalAssignmentsCreated++;

            // Buat Submisi untuk Tugas Aktif (Sebagian siswa sudah mengumpulkan dan sebagian sudah dinilai)
            $studentsActive = User::where('role', 'siswa')
                ->where('class_id', $classActive->id)
                ->get();
            if ($studentsActive->isEmpty()) {
                $studentsActive = User::where('role', 'siswa')->get();
            }

            // Misal 6 siswa pertama mengumpulkan tugas aktif
            $submittedCount = min(6, $studentsActive->count());
            for ($i = 0; $i < $submittedCount; $i++) {
                $student = $studentsActive[$i];
                $submittedTime = $activeCreated->copy()->addHours(rand(4, 36));

                // 3 siswa pertama sudah dinilai oleh guru
                $isGraded = $i < 3;
                $grade = $isGraded ? rand(80, 96) : null;
                $feedback = null;
                if ($isGraded) {
                    $feedbacks = [
                        'Pengerjaan sangat rapi, pemahaman konsep sudah sangat baik.',
                        'Langkah pengerjaan sistematis dan tepat. Pertahankan prestasimu!',
                        'Bagus sekali, analisis yang disampaikan komprehensif.',
                        'Hasil pengerjaan memuaskan, format dokumen sesuai petunjuk.',
                    ];
                    $feedback = $feedbacks[$i % count($feedbacks)];
                }

                $studentSlug = Str::slug($student->name, '_');
                $notes = [
                    'Tugas sudah saya selesaikan sesuai instruksi, terima kasih bapak/ibu.',
                    'Berikut hasil pekerjaan saya, mohon bimbingan dan koreksinya.',
                    'Sudah dilengkapi dengan lampiran sumber referensi.',
                    'File tugas terlampir dalam format PDF, terima kasih.',
                ];

                Submission::create([
                    'assignment_id' => $assignmentActive->id,
                    'student_id' => $student->id,
                    'file_path' => "dummy/submissions/tugas_{$assignmentActive->id}_{$student->id}.pdf",
                    'file_name' => "Tugas_{$subjectSlug}_{$studentSlug}.pdf",
                    'note' => $notes[$i % count($notes)],
                    'submitted_at' => $submittedTime,
                    'grade' => $grade,
                    'feedback' => $feedback,
                    'created_at' => $submittedTime,
                    'updated_at' => $submittedTime,
                ]);
                $totalSubmissionsCreated++;
            }

            // ----------------------------------------------------
            // 2. TUGAS LAMPAU / SELESAI (Deadline beberapa hari lalu)
            // ----------------------------------------------------
            $pastCreated = Carbon::now()->subDays(rand(7, 10))->subHours(rand(2, 6));
            $pastDueDate = Carbon::now()->subDays(rand(2, 4))->setTime(23, 59, 0);

            $assignmentPast = Assignment::create([
                'teacher_id' => $teacher->id,
                'school_class_id' => $classPast->id,
                'subject_id' => $subject->id,
                'title' => $config['past']['title'],
                'description' => $config['past']['description'],
                'due_date' => $pastDueDate,
                'created_at' => $pastCreated,
                'updated_at' => $pastCreated,
            ]);
            $totalAssignmentsCreated++;

            // Submisi untuk Tugas Lampau
            $studentsPast = User::where('role', 'siswa')
                ->where('class_id', $classPast->id)
                ->get();
            if ($studentsPast->isEmpty()) {
                $studentsPast = User::where('role', 'siswa')->get();
            }

            // Misal 8 siswa mengumpulkan tugas lampau (sebagian tepat waktu, 1 telat, hampir semua sudah dinilai)
            $pastSubmittedCount = min(8, $studentsPast->count());
            for ($j = 0; $j < $pastSubmittedCount; $j++) {
                $student = $studentsPast[$j];

                // j = 7 terlambat mengumpulkan sesudah deadline
                $isLate = ($j === 7);
                if ($isLate) {
                    $submittedTime = $pastDueDate->copy()->addHours(rand(2, 8));
                    $note = 'Mohon maaf bapak/ibu, saya mengumpulkan terlambat karena ada kendala jaringan.';
                    $grade = rand(75, 80);
                    $feedback = 'Terlambat mengumpulkan, ke depan harap perhatikan tenggat waktu penugasan ya.';
                } else {
                    $submittedTime = $pastCreated->copy()->addDays(rand(1, 3))->addHours(rand(1, 10));
                    $note = 'Tugas lengkap beserta rangkuman materi.';
                    // 1 siswa belum dinilai, sisanya sudah dinilai
                    $isGraded = ($j < 6);
                    $grade = $isGraded ? rand(82, 98) : null;
                    $feedback = $isGraded ? 'Sangat memuaskan, penjelasan mendalam dan tepat sasaran.' : null;
                }

                $studentSlug = Str::slug($student->name, '_');

                Submission::create([
                    'assignment_id' => $assignmentPast->id,
                    'student_id' => $student->id,
                    'file_path' => "dummy/submissions/tugas_{$assignmentPast->id}_{$student->id}.pdf",
                    'file_name' => "Rangkuman_{$subjectSlug}_{$studentSlug}.pdf",
                    'note' => $note,
                    'submitted_at' => $submittedTime,
                    'grade' => $grade,
                    'feedback' => $feedback,
                    'created_at' => $submittedTime,
                    'updated_at' => $submittedTime,
                ]);
                $totalSubmissionsCreated++;
            }
        }

        if ($this->command) {
            $this->command->info("AssignmentSeeder: Berhasil membuat {$totalAssignmentsCreated} tugas dan {$totalSubmissionsCreated} pengumpulan untuk " . $teachers->count() . " guru.");
        }
    }
}
