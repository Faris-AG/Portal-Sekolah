<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Models\User;
use App\Models\Schedule;
use App\Models\ForumTopic;
use App\Models\ForumReply;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ForumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classes = SchoolClass::all();

        if ($classes->isEmpty()) {
            if ($this->command) {
                $this->command->warn('Tidak ada data kelas (SchoolClass). Pastikan seeder kelas telah dijalankan.');
            }
            return;
        }

        // Hapus forum reply & topic sebelumnya untuk mencegah duplikasi data
        ForumReply::query()->delete();
        ForumTopic::query()->delete();

        // Template konten realistis per kelas
        $curatedClassData = [
            'X MIPA 1' => [
                'academic' => [
                    'title' => 'Diskusi & Tanya Jawab Materi Bab 1: Sistem Persamaan Linear (SPLTV)',
                    'content' => 'Halo semua, jika ada kesulitan dalam menyelesaikan soal latihan eliminasi dan substitusi pada Bab 1, silakan tanyakan di forum ini ya. Cantumkan nomor soal di buku latihan dan langkah yang membingungkan.',
                    'replies' => [
                        ['role' => 'siswa', 'content' => 'Selamat siang Pak, untuk latihan nomor 4 bagian B apakah variabel z yang sebaiknya dieliminasi lebih dulu dari persamaan 1 dan 2?'],
                        ['role' => 'guru', 'content' => 'Bagus sekali pertanyaannya. Mengeliminasi variabel z terlebih dahulu dari persamaan 1 dan 2 adalah langkah tercepat karena koefisiennya sudah sama.'],
                        ['role' => 'siswa', 'content' => 'Paham Pak, terima kasih penjelasannya! Tadi saya sempat ragu karena hasil akhirnya berbentuk pecahan.'],
                        ['role' => 'siswa', 'content' => 'Saya juga sudah coba cek dengan metode determinan matriks dan hasilnya sama. Terima kasih diskusinya teman-teman!'],
                    ],
                ],
                'general' => [
                    'title' => 'Koordinasi Kelompok Belajar & Persiapan Ulangan Harian',
                    'content' => 'Teman-teman X MIPA 1, bagaimana kalau kita buat kelompok belajar bersama untuk persiapan ulangan harian minggu depan? Yang berminat kumpul di perpustakaan sekolah sepulang jam pelajaran ya.',
                    'replies' => [
                        ['role' => 'siswa', 'content' => 'Boleh banget! Mau mulai kumpul hari apa? Kebetulan aku butuh review materi hitungan dan pembagian waktu latihan.'],
                        ['role' => 'siswa', 'content' => 'Fokus bahas latihan soal bab 1 dulu yuk, nanti aku bawa rangkuman rumus yang sudah dirapikan semalam.'],
                        ['role' => 'guru', 'content' => 'Inisiatif yang sangat baik anak-anak. Manfaatkan ruang baca perpustakaan dengan tertib dan jaga ketenangan ya. Semoga sukses ulangannya!'],
                        ['role' => 'siswa', 'content' => 'Siap Pak, terima kasih banyak atas dukungannya! Nanti hari Rabu sepulang sekolah kita kumpul ya kawan-kawan.'],
                    ],
                ],
            ],
            'X MIPA 2' => [
                'academic' => [
                    'title' => 'Klarifikasi & Diskusi Laporan Praktikum Pengukuran dan Vektor',
                    'content' => 'Silakan gunakan thread ini untuk mendiskusikan format laporan praktikum jangka sorong dan mikrometer sekrup. Batas pengumpulan laporan adalah hari Senin pekan depan.',
                    'replies' => [
                        ['role' => 'siswa', 'content' => 'Pak, untuk tabel analisis ketidakpastian pengukuran apakah wajib ditulis tangan atau boleh diketik rapi?'],
                        ['role' => 'guru', 'content' => 'Tabel analisis ketidakpastian wajib ditulis tangan rapi beserta penjabaran langkah perhitungannya ya, agar kalian memahami prosesnya secara mandiri.'],
                        ['role' => 'siswa', 'content' => 'Baik Pak, untuk perhitungan nilai skala terkecil (NST) mikrometer sekrup 0,01 mm sudah jelas.'],
                        ['role' => 'siswa', 'content' => 'Terima kasih arahannya Pak, grafik hubungan pengukurannya juga sudah selesai kami susun bersama tim.'],
                    ],
                ],
                'general' => [
                    'title' => 'Pertanyaan Terkait Jadwal & Perlengkapan Praktikum Kimia',
                    'content' => 'Teman-teman, untuk praktikum kimia hari Rabu besok apakah jas laboratorium dan sarung tangan lateks harus dibawa mandiri dari rumah?',
                    'replies' => [
                        ['role' => 'siswa', 'content' => 'Iya, wajib bawa jas lab putih dan masker sendiri. Jangan lupa bawa kain lap bersih juga untuk meja lab.'],
                        ['role' => 'siswa', 'content' => 'Untuk modul panduan praktikumnya apakah perlu di-print sendiri atau sudah ada di meja lab?'],
                        ['role' => 'guru', 'content' => 'Modul praktikum sudah disediakan di laboratorium, anak-anak cukup membawa jas lab, alat tulis, dan mematuhi tata tertib keselamatan kerja.'],
                    ],
                ],
            ],
            'X IPS 1' => [
                'academic' => [
                    'title' => 'Diskusi Studi Kasus: Kelangkaan Sumber Daya & Skala Prioritas',
                    'content' => 'Anak-anak kelas X IPS 1, setelah mempelajari konsep kelangkaan ekonomi, coba berikan contoh nyata pengelolaan skala prioritas kebutuhan yang kalian terapkan dalam kehidupan sehari-hari.',
                    'replies' => [
                        ['role' => 'siswa', 'content' => 'Izin menjawab Bu, contohnya mengalokasikan uang saku untuk membeli buku pelajaran dan kuota internet riset terlebih dahulu sebelum pengeluaran hiburan.'],
                        ['role' => 'guru', 'content' => 'Contoh yang sangat baik dan aplikatif! Ada yang ingin menambahkan bagaimana cara mengatasi biaya peluang (opportunity cost)?'],
                        ['role' => 'siswa', 'content' => 'Dengan membuat daftar tabel prioritas kebutuhan primer, sekunder, dan tersier Bu, sehingga keputusan yang diambil rasional.'],
                        ['role' => 'siswa', 'content' => 'Diskusi ini membantu sekali untuk menyusun draf tugas esai ekonomi minggu ini. Terima kasih Bu guru dan teman-teman!'],
                    ],
                ],
                'general' => [
                    'title' => 'Pembagian Kelompok Presentasi Studi Lapangan Sosiologi',
                    'content' => 'Halo teman-teman X IPS 1! Kita perlu membagi kelompok menjadi 5 tim untuk observasi interaksi sosial. Ada teman-teman yang belum mendapatkan kelompok?',
                    'replies' => [
                        ['role' => 'siswa', 'content' => 'Kelompok 2 kami masih kurang 1 orang nih, ada yang mau gabung untuk bagian analisis data lapangan?'],
                        ['role' => 'siswa', 'content' => 'Aku belum ada kelompok, boleh gabung ke kelompok 2 ya? Nanti aku bantu dokumentasi dan pembuatan slidenya.'],
                        ['role' => 'guru', 'content' => 'Bagus sekali koordinasinya. Pastikan setiap anggota kelompok berkontribusi aktif dan hasil observasinya objektif ya.'],
                    ],
                ],
            ],
            'XI MIPA 1' => [
                'academic' => [
                    'title' => 'Tanya Jawab Materi Bab 2: Struktur Sel dan Transpor Membran',
                    'content' => 'Halo semuanya, jika ada konsep difusi, osmosis, atau transpor aktif yang masih membingungkan silakan ditanyakan di sini untuk persiapan kuis lusa.',
                    'replies' => [
                        ['role' => 'siswa', 'content' => 'Ibu, apa perbedaan esensial antara difusi terfasilitasi dengan transpor aktif jika keduanya sama-sama memanfaatkan saluran protein?'],
                        ['role' => 'guru', 'content' => 'Perbedaannya terletak pada kebutuhan energi ATP dan arah gradien. Transpor aktif membutuhkan ATP karena memompa zat melawan gradien konsentrasi.'],
                        ['role' => 'siswa', 'content' => 'Wah langsung jelas Bu! Berarti kalau difusi terfasilitasi itu pasif mengikuti gradien tanpa memerlukan ATP ya.'],
                        ['role' => 'siswa', 'content' => 'Terima kasih penjelasannya Bu! Sekarang jadi lebih siap menghadapi kuis biologi besok.'],
                    ],
                ],
                'general' => [
                    'title' => 'Sharing Catatan Ringkas & Bank Soal Latihan Mandiri',
                    'content' => 'Teman-teman XI MIPA 1, aku sudah merangkum konsep stoikiometri dan rumus fisika gerak harmonik sederhana. Siapa tahu bermanfaat untuk belajar bersama menjelang asesmen.',
                    'replies' => [
                        ['role' => 'siswa', 'content' => 'Keren banget catatannya rapi dan ada contoh soalnya. Terima kasih banyak ya sudah berbagi!'],
                        ['role' => 'siswa', 'content' => 'Sangat membantu, terutama rumus turunan getaran harmonik dan fasenya.'],
                        ['role' => 'guru', 'content' => 'Semangat saling berbagi ilmu yang sangat terpuji. Tetap cross-check dengan materi di buku referensi resmi ya anak-anak.'],
                    ],
                ],
            ],
            'XI MIPA 2' => [
                'academic' => [
                    'title' => 'Klarifikasi Soal Latihan Mandiri: Dinamika Rotasi & Kesetimbangan',
                    'content' => 'Anak-anak kelas XI MIPA 2, silakan manfaatkan utas ini untuk mengonfirmasi solusi latihan soal momen inersia dan torsi yang kita bahas pada pertemuan kemarin.',
                    'replies' => [
                        ['role' => 'siswa', 'content' => 'Pak, untuk soal silinder pejal yang menggelinding di bidang miring, apakah energi kinetik rotasi harus selalu dijumlahkan dengan translasi?'],
                        ['role' => 'guru', 'content' => 'Ya, karena silinder mengalami gerak translasi dan rotasi secara bersamaan, maka energi kinetik total adalah EK translasi ditambah EK rotasi.'],
                        ['role' => 'siswa', 'content' => 'Paham Pak, pantas tadi perhitungan percepatan saya kurang faktor inersianya. Terima kasih banyak Pak!'],
                        ['role' => 'guru', 'content' => 'Sama-sama nak. Coba hitung ulang dengan substitusi I = 1/2 m r^2 ya.'],
                    ],
                ],
                'general' => [
                    'title' => 'Rencana Pengadaan Kas Kelas & Kebersihan Ruang Belajar',
                    'content' => 'Teman-teman XI MIPA 2, kas kelas kita bulan ini perlu dialokasikan untuk pengadaan spidol whiteboard baru, penghapus, dan tanaman hias kelas. Usul iuran mingguan disepakati bersama ya.',
                    'replies' => [
                        ['role' => 'siswa', 'content' => 'Setuju, nanti bendahara kelas bisa mulai rekap setiap hari Senin pagi.'],
                        ['role' => 'siswa', 'content' => 'Sekalian kita jadwalkan piket harian diperketat supaya kelas tetap nyaman dan bersih untuk belajar.'],
                        ['role' => 'guru', 'content' => 'Bapak/Ibu selaku wali kelas sangat mengapresiasi kekompakan kalian. Mari ciptakan suasana kelas yang nyaman dan kondusif.'],
                    ],
                ],
            ],
            'XII MIPA 1' => [
                'academic' => [
                    'title' => 'Pendalaman Materi: Limit Fungsi Trigonometri & Kalkulus Lanjut',
                    'content' => 'Untuk seluruh siswa kelas XII MIPA 1, silakan diskusikan soal-soal penalaran limit trigonometri dan kalkulus diferensial di sini sebagai persiapan ujian sekolah dan tryout.',
                    'replies' => [
                        ['role' => 'siswa', 'content' => 'Pak, untuk bentuk tak tentu 0/0 pada limit fungsi trigonometri, apakah teorema L\'Hopital selalu boleh digunakan?'],
                        ['role' => 'guru', 'content' => 'Pada soal esai, utamakan pembuktian dengan rumus dasar identitas trigonometri. Teorema L\'Hopital dapat digunakan sebagai verifikasi cepat pada soal pilihan ganda.'],
                        ['role' => 'siswa', 'content' => 'Baik Pak, arahan yang sangat membantu. Kami akan latihan kedua metodenya agar terbiasa.'],
                        ['role' => 'siswa', 'content' => 'Terima kasih banyak Pak atas tips dan bimbingannya untuk persiapan kami.'],
                    ],
                ],
                'general' => [
                    'title' => 'Informasi Tryout UTBK/SNBT & Diskusi Jurusan Perguruan Tinggi',
                    'content' => 'Halo kawan-kawan kelas XII! Ada info tryout nasional gratis untuk subtes Potensi Kognitif dan Penalaran Matematika Sabtu ini. Yuk saling sharing info tryout dan strategi pemilihan jurusan PTN.',
                    'replies' => [
                        ['role' => 'siswa', 'content' => 'Boleh minta link pendaftarannya? Aku sedang rutin latihan soal penalaran analitis dan literasi bahasa.'],
                        ['role' => 'siswa', 'content' => 'Link sudah aku bagikan ya. Jangan lupa ikuti juga sesi bedah passing grade jurusan kampus impian.'],
                        ['role' => 'guru', 'content' => 'Tetap semangat berjuang anak-anakku kelas 12! Jaga kesehatan, fokus pada target, dan pintu konsultasi guru BK selalu terbuka untuk kalian.'],
                        ['role' => 'siswa', 'content' => 'Aamiin, terima kasih banyak atas doa dan bimbingan bapak/ibu guru tercinta!'],
                    ],
                ],
            ],
        ];

        // Loop ke setiap kelas
        foreach ($classes as $class) {
            // Ambil guru yang mengajar di kelas ini (via Jadwal atau Wali Kelas)
            $classTeachers = Schedule::where('school_class_id', $class->id)
                ->with('teacher.subject')
                ->get()
                ->pluck('teacher')
                ->filter()
                ->unique('id')
                ->values();

            if ($classTeachers->isEmpty() && $class->teacher_id) {
                $wali = User::find($class->teacher_id);
                if ($wali) {
                    $classTeachers = collect([$wali]);
                }
            }

            if ($classTeachers->isEmpty()) {
                $classTeachers = User::where('role', 'guru')->get();
            }

            // Ambil siswa di kelas ini
            $students = User::where('role', 'siswa')
                ->where('class_id', $class->id)
                ->get();

            if ($students->isEmpty()) {
                $students = User::where('role', 'siswa')->get();
            }

            $primaryTeacher = $classTeachers->first();
            $studentAuthor = $students->count() > 2 ? $students[2] : $students->first();

            // Dapatkan template konten untuk kelas ini
            $classConfig = $curatedClassData[$class->name] ?? null;

            if (!$classConfig) {
                $subjectName = $primaryTeacher?->subject?->name ?? 'Mata Pelajaran';
                $classConfig = [
                    'academic' => [
                        'title' => "Diskusi & Tanya Jawab Materi {$subjectName} - Kelas {$class->name}",
                        'content' => "Selamat datang di forum diskusi materi {$subjectName}. Silakan ajukan pertanyaan atau kendala yang dihadapi saat mempelajari modul Bab ini.",
                        'replies' => [
                            ['role' => 'siswa', 'content' => 'Selamat pagi Bapak/Ibu guru, izin bertanya untuk soal latihan nomor 3 cara penyelesaiannya bagaimana ya?'],
                            ['role' => 'guru', 'content' => 'Halo nak, untuk soal nomor 3 perhatikan kembali konsep dasar di modul halaman 15 dan mulai dengan mengidentifikasi variabel yang diketahui.'],
                            ['role' => 'siswa', 'content' => 'Terima kasih banyak atas penjelasannya Bapak/Ibu, sekarang sudah jauh lebih paham.'],
                        ],
                    ],
                    'general' => [
                        'title' => "Koordinasi Belajar & Informasi Kelas {$class->name}",
                        'content' => "Halo teman-teman {$class->name}, forum ini dibuat untuk koordinasi kelompok belajar, jadwal tugas, dan kegiatan bersama di kelas kita.",
                        'replies' => [
                            ['role' => 'siswa', 'content' => 'Terima kasih atas inisiatifnya. Jangan lupa untuk tugas kelompok minggu ini kita selesaikan sebelum tenggat waktu ya.'],
                            ['role' => 'siswa', 'content' => 'Setuju, mari saling bantu jika ada materi yang belum dipahami.'],
                            ['role' => 'guru', 'content' => 'Bagus sekali koordinasinya anak-anak. Tetap kompak dan saling mendukung dalam belajar ya!'],
                        ],
                    ],
                ];
            }

            // ==========================================
            // TOPIK 1: Akademik / Tugas (Dibuat oleh Guru)
            // ==========================================
            $topic1Time = Carbon::now()->subDays(rand(2, 4))->subHours(rand(2, 8));
            $academicData = $classConfig['academic'];

            $topic1 = ForumTopic::create([
                'school_class_id' => $class->id,
                'user_id' => $primaryTeacher->id,
                'title' => $academicData['title'],
                'content' => $academicData['content'],
                'created_at' => $topic1Time,
                'updated_at' => $topic1Time,
            ]);

            $replyTime = $topic1Time->copy();
            $studentIndex = 0;

            foreach ($academicData['replies'] as $replyInfo) {
                $replyTime = $replyTime->copy()->addMinutes(rand(25, 90));

                if ($replyInfo['role'] === 'guru') {
                    $replyUserId = $primaryTeacher->id;
                } else {
                    $replyUser = $students[$studentIndex % $students->count()];
                    $replyUserId = $replyUser->id;
                    $studentIndex++;
                }

                ForumReply::create([
                    'forum_topic_id' => $topic1->id,
                    'user_id' => $replyUserId,
                    'content' => $replyInfo['content'],
                    'created_at' => $replyTime,
                    'updated_at' => $replyTime,
                ]);
            }

            $topic1->update(['updated_at' => $replyTime]);

            // ==========================================
            // TOPIK 2: Umum / Kelas (Dibuat oleh Siswa)
            // ==========================================
            $topic2Time = Carbon::now()->subDays(rand(1, 2))->subHours(rand(1, 6));
            $generalData = $classConfig['general'];

            $topic2 = ForumTopic::create([
                'school_class_id' => $class->id,
                'user_id' => $studentAuthor->id,
                'title' => $generalData['title'],
                'content' => $generalData['content'],
                'created_at' => $topic2Time,
                'updated_at' => $topic2Time,
            ]);

            $replyTime2 = $topic2Time->copy();
            $studentReplyIndex = 1;

            foreach ($generalData['replies'] as $replyInfo) {
                $replyTime2 = $replyTime2->copy()->addMinutes(rand(20, 80));

                if ($replyInfo['role'] === 'guru') {
                    // Pakai wali kelas atau guru kedua jika ada
                    $teacherUser = $classTeachers->count() > 1 ? $classTeachers[1] : $primaryTeacher;
                    $replyUserId = $teacherUser->id;
                } else {
                    $replyUser = $students[$studentReplyIndex % $students->count()];
                    $replyUserId = $replyUser->id;
                    $studentReplyIndex++;
                }

                ForumReply::create([
                    'forum_topic_id' => $topic2->id,
                    'user_id' => $replyUserId,
                    'content' => $replyInfo['content'],
                    'created_at' => $replyTime2,
                    'updated_at' => $replyTime2,
                ]);
            }

            $topic2->update(['updated_at' => $replyTime2]);
        }

        if ($this->command) {
            $this->command->info('ForumSeeder: Berhasil membuat data dummy forum diskusi untuk ' . $classes->count() . ' kelas.');
        }
    }
}
