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

        // Template konten realistis per kelas (SMK PPLG / BCF / Animasi / TKR / TPFL)
        $curatedClassData = [
            'X PPLG 1' => [
                'academic' => [
                    'title' => 'Diskusi & Tanya Jawab: Dasar Pemrograman Python & Logika Algoritma',
                    'content' => 'Halo teman-teman X PPLG 1! Thread ini untuk diskusi materi Dasar Pemrograman — khususnya sintaks Python, percabangan if-elif-else, dan perulangan. Silakan tanyakan kendala yang dihadapi saat mengerjakan latihan Bab 1.',
                    'replies' => [
                        ['role' => 'siswa', 'content' => 'Pak, untuk soal nomor 5 yang perulangan bersarang (nested loop), kenapa output-nya tidak simetris? Padahal sudah mengikuti contoh di modul.'],
                        ['role' => 'guru', 'content' => 'Coba perhatikan lagi indentasi kode Python-nya ya. Python sangat sensitif terhadap spasi — satu tab yang salah bisa mengubah alur logika secara total.'],
                        ['role' => 'siswa', 'content' => 'Oh iya Pak! Ternyata baris print-nya masuk ke dalam loop yang salah. Sekarang sudah benar dan outputnya sudah simetris. Terima kasih!'],
                        ['role' => 'siswa', 'content' => 'Bro yang lain, kita bisa bikin grup belajar coding bareng nggak? Nanti bisa saling review code masing-masing.'],
                    ],
                ],
                'general' => [
                    'title' => 'Koordinasi Kelompok Project Akhir PPLG & Pembagian Role Tim',
                    'content' => 'Teman-teman X PPLG 1, kita perlu segera membentuk kelompok untuk project akhir semester — membuat aplikasi berbasis web sederhana. Siapa saja yang mau bergabung? Tentukan juga role masing-masing: UI Designer, Back-end Dev, atau QA Tester.',
                    'replies' => [
                        ['role' => 'siswa', 'content' => 'Saya mau jadi Back-end Dev! Sudah mulai belajar PHP dan Laravel sendiri di rumah.'],
                        ['role' => 'siswa', 'content' => 'Aku ambil bagian UI/UX ya, sudah ada mockup kasar di Figma nih kalau mau dilihat.'],
                        ['role' => 'guru', 'content' => 'Bagus sekali inisiatifnya! Pastikan setiap anggota tim punya kontribusi nyata ya. Dokumentasikan progress kalian secara berkala.'],
                        ['role' => 'siswa', 'content' => 'Noted Pak. Kita akan buat repository Git bersama dan jadwalkan daily stand-up tiap Rabu sore.'],
                    ],
                ],
            ],
            'X BCF 1' => [
                'academic' => [
                    'title' => 'Diskusi Produksi Konten: Teknik Pengambilan Gambar & Komposisi Visual',
                    'content' => 'Untuk materi Produksi Konten Digital pekan ini, kita membahas teknik dasar sinematografi — rule of thirds, framing, dan angle kamera. Silakan share hasil foto/video latihan kalian di sini untuk direview bersama.',
                    'replies' => [
                        ['role' => 'siswa', 'content' => 'Bu, saat pengambilan gambar di luar ruangan, settingan aperture yang disarankan berapa untuk kondisi cahaya terang?'],
                        ['role' => 'guru', 'content' => 'Untuk kondisi cahaya terang di luar, coba mulai dari aperture f/8 dengan ISO 100. Eksperimen dan perhatikan hasilnya — fotografi itu soal rasa dan latihan.'],
                        ['role' => 'siswa', 'content' => 'Terima kasih Bu! Hasil foto tadi jauh lebih natural dibanding sebelumnya yang overexposed.'],
                    ],
                ],
                'general' => [
                    'title' => 'Info & Persiapan Lomba Film Pendek Tingkat Kabupaten Bogor',
                    'content' => 'Teman-teman BCF! Ada info lomba film pendek tingkat kabupaten Bogor bulan depan. Tema: "Keberagaman Budaya". Siapa yang tertarik ikut mewakili SKANIC? Butuh tim sutradara, kameramen, pemeran, dan editor.',
                    'replies' => [
                        ['role' => 'siswa', 'content' => 'Aku mau jadi editor videonya! Sudah biasa pakai DaVinci Resolve untuk proyek kelas.'],
                        ['role' => 'siswa', 'content' => 'Saya bisa jadi kameramen sekaligus bantu lighting setup. Kebetulan sudah punya tripod dan reflektor sendiri.'],
                        ['role' => 'guru', 'content' => 'Semangat anak-anak SKANIC! Koordinasikan dulu dengan guru pembimbing ekskul broadcasting untuk teknis pendaftarannya ya.'],
                    ],
                ],
            ],
            'X Animasi 1' => [
                'academic' => [
                    'title' => 'Tanya Jawab: Prinsip Animasi 12 Dasar & Teknik Frame-by-Frame',
                    'content' => 'Halo semua, untuk persiapan UTS Teknik Animasi, kita review 12 prinsip animasi Disney ya — Squash & Stretch, Anticipation, Staging, Straight-Ahead, Pose-to-Pose, dsb. Ada yang mau ditanyakan?',
                    'replies' => [
                        ['role' => 'siswa', 'content' => 'Pak, bedanya teknik Straight-Ahead dengan Pose-to-Pose itu apa? Keduanya sama-sama membuat gerakan, tapi sepertinya punya kegunaan berbeda.'],
                        ['role' => 'guru', 'content' => 'Straight-Ahead dibuat berurutan dari frame 1 hingga selesai — hasilnya lebih spontan dan natural untuk gerakan bebas. Pose-to-Pose menentukan keyframe dulu, lalu mengisi inbetween — lebih presisi untuk gestur karakter tertentu.'],
                        ['role' => 'siswa', 'content' => 'Paham sekarang Pak! Berarti untuk animasi bola memantul lebih cocok Straight-Ahead ya.'],
                    ],
                ],
                'general' => [
                    'title' => 'Sharing Software & Aset: Rekomendasi Tools Animasi 2D Gratis',
                    'content' => 'Teman-teman Animasi, ada yang sudah coba software selain Adobe Animate untuk bikin animasi 2D? Mau sharing tools gratis yang bagus untuk latihan di rumah.',
                    'replies' => [
                        ['role' => 'siswa', 'content' => 'Aku pakai OpenToonz, lumayan powerful dan gratis. Ada library brush bawaan yang cukup lengkap juga.'],
                        ['role' => 'siswa', 'content' => 'Kalau Pencil2D juga bagus buat pemula, interface-nya simpel dan bisa export ke berbagai format video.'],
                        ['role' => 'guru', 'content' => 'Bagus sekali! Eksplorasi berbagai tools itu penting. Yang terpenting adalah pahami prinsip animasinya — software hanya alat.'],
                    ],
                ],
            ],
            'X TKR 1' => [
                'academic' => [
                    'title' => 'Diskusi & Tanya Jawab: Sistem Pemindah Tenaga & Komponen Kopling',
                    'content' => 'Untuk persiapan praktik minggu depan, kita bahas dulu teori komponen sistem kopling manual — plat kopling, flywheel, dan cara kerja release fork. Ada pertanyaan dari materi yang sudah dibaca?',
                    'replies' => [
                        ['role' => 'siswa', 'content' => 'Pak, apa fungsi utama torsion spring yang ada di plat kopling? Apakah ini selalu ada di semua jenis kopling manual?'],
                        ['role' => 'guru', 'content' => 'Torsion spring berfungsi meredam getaran dan kejutan saat kopling berhubungan kembali, agar perpindahan tenaga tidak kasar. Tidak semua kopling memakai ini — tergantung desain dan tujuan kendaraan.'],
                        ['role' => 'siswa', 'content' => 'Terima kasih Pak, sekarang lebih jelas. Berarti pada kendaraan motorsport kadang sengaja dihilangkan agar respons kopling lebih cepat ya?'],
                        ['role' => 'guru', 'content' => 'Tepat sekali! Kamu sudah mulai berpikir seperti teknisi yang baik.'],
                    ],
                ],
                'general' => [
                    'title' => 'Info Jadwal Praktik & Persiapan Alat K3 Bengkel TKR',
                    'content' => 'Teman-teman X TKR 1, jadwal praktik bongkar pasang sistem kopling manual dijadwalkan Rabu pagi. Pastikan membawa wearpack, safety shoes, kacamata pelindung, dan sarung tangan sesuai SOP K3 bengkel.',
                    'replies' => [
                        ['role' => 'siswa', 'content' => 'Pak, untuk praktik bongkar kopling apakah perlu bawa kunci momen sendiri atau sudah tersedia di bengkel sekolah?'],
                        ['role' => 'guru', 'content' => 'Kunci momen tersedia di bengkel, tapi kalian wajib pakai sarung tangan dan wearpack. Tidak ada pengecualian soal K3 di bengkel!'],
                        ['role' => 'siswa', 'content' => 'Siap Pak! Kami akan pastikan semua APD lengkap sebelum masuk bengkel.'],
                    ],
                ],
            ],
            'X TPFL 1' => [
                'academic' => [
                    'title' => 'Tanya Jawab Materi: Teknik Pengelasan SMAW & Standar Sambungan Las',
                    'content' => 'Thread ini untuk diskusi materi Shielded Metal Arc Welding (SMAW) — polaritas elektroda, penyetelan ampere, dan jenis sambungan las (butt joint, T-joint, lap joint). Tanyakan kendala kalian di sini.',
                    'replies' => [
                        ['role' => 'siswa', 'content' => 'Pak, saat mencoba mengelas T-joint kemarin hasil las-an saya berpori-pori. Apa penyebab utamanya?'],
                        ['role' => 'guru', 'content' => 'Porositas biasanya disebabkan elektroda yang lembab, permukaan benda kerja yang kotor atau berkarat, atau ampere terlalu rendah. Pastikan elektroda disimpan dalam oven penyimpan dan benda kerja dibersihkan dulu dengan wire brush.'],
                        ['role' => 'siswa', 'content' => 'Oh ternyata elektrodanya terkena lembab dari gudang. Akan saya perhatikan lagi Pak, terima kasih!'],
                    ],
                ],
                'general' => [
                    'title' => 'Persiapan Uji Kompetensi Las & Sertifikasi Welder Muda SKANIC',
                    'content' => 'Info penting! Bulan depan ada program Uji Kompetensi Las bekerja sama dengan LSP (Lembaga Sertifikasi Profesi) untuk mendapatkan sertifikat welder muda. Ini kesempatan emas untuk CV kalian. Siapa yang mau daftar?',
                    'replies' => [
                        ['role' => 'siswa', 'content' => 'Saya mau daftar Pak! Apakah ada persyaratan khusus atau minimal jam praktik yang harus dipenuhi?'],
                        ['role' => 'siswa', 'content' => 'Aku juga mau daftar. Kalau sudah punya sertifikat welder, peluang kerja di industri manufaktur jadi lebih terbuka.'],
                        ['role' => 'guru', 'content' => 'Semangat anak-anak! Persyaratan lengkap akan dibagikan minggu depan. Pastikan nilai praktik las kalian sudah di atas KKM ya.'],
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
