<?php

namespace Database\Seeders;

use App\Models\Material;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teachers = User::whereIn('role', ['guru', 'teacher'])->with('subject')->get();
        $classes = SchoolClass::all();

        if ($teachers->isEmpty() || $classes->isEmpty()) {
            if ($this->command) {
                $this->command->warn('Data guru atau kelas belum tersedia. Harap jalankan seeder user dan kelas terlebih dahulu.');
            }
            return;
        }

        // Bersihkan data materi sebelumnya agar tidak terjadi duplikasi (idempoten)
        Material::query()->delete();

        // Kamus bahan ajar per mata pelajaran dan jenjang kelas (10, 11, 12)
        $curatedMaterials = [
            'Informatika' => [
                10 => [
                    [
                        'title' => 'Dasar Pemrograman Web & Algoritma',
                        'description' => 'Silakan unduh dan pelajari modul sintaks dasar HTML, CSS, JavaScript, dan flowchart logika pemrograman sebelum pertemuan tatap muka.',
                        'file_name' => 'Modul_Dasar_Pemrograman_Web.pdf',
                        'file_path' => 'materials/modul_pemrograman_web.pdf',
                        'link_url' => null,
                    ],
                    [
                        'title' => 'Video Pembelajaran: Berpikir Komputasional & Struktur Kontrol',
                        'description' => 'Simak video tutorial implementasi percabangan if-else dan perulangan loop untuk pemula.',
                        'file_name' => null,
                        'file_path' => null,
                        'link_url' => 'https://youtube.com/watch?v=inf_komputasional_x',
                    ],
                ],
                11 => [
                    [
                        'title' => 'Struktur Data Lanjut & Pemrograman Berorientasi Objek (OOP)',
                        'description' => 'Bahan ajar konsep array multidimensi, queue, stack, dan perancangan kelas/objek pada Python.',
                        'file_name' => 'Slide_Struktur_Data_OOP.pptx',
                        'file_path' => 'materials/slide_struktur_data_oop.pptx',
                        'link_url' => 'https://docs.google.com/presentation/d/oop_inf_xi',
                    ],
                ],
                12 => [
                    [
                        'title' => 'Arsitektur Basis Data Relasional & Keamanan Siber',
                        'description' => 'Modul perancangan skema ERD, normalisasi tabel SQL, dan prinsip kriptografi dasar untuk keamanan informasi.',
                        'file_name' => 'Modul_Basis_Data_Keamanan.pdf',
                        'file_path' => 'materials/modul_basis_data_keamanan.pdf',
                        'link_url' => 'https://github.com/sekolah/database-security-docs',
                    ],
                ],
            ],

            'Matematika Wajib' => [
                10 => [
                    [
                        'title' => 'Konsep Dasar Matriks & Determinan Ordo 2x2 dan 3x3',
                        'description' => 'Pelajari konsep operasi aljabar matriks, invers matriks, dan penentuan nilai determinan metode Sarrus.',
                        'file_name' => 'Modul_Matriks_Determinan_X.pdf',
                        'file_path' => 'materials/modul_matriks_determinan_x.pdf',
                        'link_url' => null,
                    ],
                    [
                        'title' => 'Video Pembahasan: Sistem Persamaan Linear Tiga Variabel (SPLTV)',
                        'description' => 'Video panduan langkah eliminasi dan substitusi SPLTV secara runtut dan sistematis.',
                        'file_name' => null,
                        'file_path' => null,
                        'link_url' => 'https://youtube.com/watch?v=mtk_spltv_cramer',
                    ],
                ],
                11 => [
                    [
                        'title' => 'Barisan, Deret Aritmatika & Geometri Terapan',
                        'description' => 'Modul rumus suku ke-n, jumlah deret tak hingga, serta aplikasi pada bunga majemuk dan anuitas keuangan.',
                        'file_name' => 'Rangkuman_Barisan_Deret_XI.pdf',
                        'file_path' => 'materials/rangkuman_barisan_deret_xi.pdf',
                        'link_url' => 'https://example.com/math-series-notes',
                    ],
                ],
                12 => [
                    [
                        'title' => 'Kalkulus: Turunan Fungsi Aljabar dan Integral Tentu',
                        'description' => 'Modul pendalaman kaidah diferensial, penentuan gradien garis singgung kurva, dan luas daerah kurva.',
                        'file_name' => 'Modul_Kalkulus_Integral_XII.pdf',
                        'file_path' => 'materials/modul_kalkulus_integral_xii.pdf',
                        'link_url' => 'https://youtube.com/watch?v=mtk_kalkulus_xii',
                    ],
                ],
            ],

            'Bahasa Indonesia' => [
                10 => [
                    [
                        'title' => 'Analisis Struktur dan Kaidah Teks Laporan Hasil Observasi',
                        'description' => 'Panduan menyusun teks LHO yang objektif, faktual, dan menggunakan verba relasional sesuai kaidah kebahasaan.',
                        'file_name' => 'Modul_Teks_LHO_X.pdf',
                        'file_path' => 'materials/modul_teks_lho_x.pdf',
                        'link_url' => null,
                    ],
                ],
                11 => [
                    [
                        'title' => 'Struktur dan Kebahasaan Teks Eksplanasi Fenomena Alam & Sosial',
                        'description' => 'Bahan ajar pola hubungan kausalitas, urutan kronologis, dan konjungsi penerang dalam teks eksplanasi ilmiah.',
                        'file_name' => 'Modul_Teks_Eksplanasi_XI.pdf',
                        'file_path' => 'materials/modul_teks_eksplanasi_xi.pdf',
                        'link_url' => 'https://youtube.com/watch?v=bindo_eksplanasi_xi',
                    ],
                ],
                12 => [
                    [
                        'title' => 'Teknik Penulisan Surat Lamaran Pekerjaan dan CV Profesional',
                        'description' => 'Pedoman format baku surat dinas, portofolio profesional, dan penggunaan ragam bahasa baku EYD edisi V.',
                        'file_name' => 'Panduan_Surat_Lamaran_XII.docx',
                        'file_path' => 'materials/panduan_surat_lamaran_xii.docx',
                        'link_url' => null,
                    ],
                ],
            ],

            'Bahasa Inggris' => [
                10 => [
                    [
                        'title' => 'Descriptive Text: Describing Places and Historical Monuments',
                        'description' => 'Handout on specific vocabulary, sensory details, and identification-description paragraph organization.',
                        'file_name' => 'Handout_Descriptive_Text_X.pdf',
                        'file_path' => 'materials/handout_descriptive_text_x.pdf',
                        'link_url' => null,
                    ],
                ],
                11 => [
                    [
                        'title' => 'Analytical Exposition Text: Crafting Persuasive Arguments',
                        'description' => 'Guidelines for constructing a strong thesis statement, supporting evidence, and causal conjunctions.',
                        'file_name' => 'Guide_Analytical_Exposition_XI.pdf',
                        'file_path' => 'materials/guide_analytical_exposition_xi.pdf',
                        'link_url' => 'https://youtube.com/watch?v=bing_exposition_xi',
                    ],
                ],
                12 => [
                    [
                        'title' => 'News Item & Academic Vocabulary Mastery',
                        'description' => 'Modul reading comprehension teks berita aktual, headline analysis, and formal academic writing conventions.',
                        'file_name' => 'Modul_News_Item_XII.pdf',
                        'file_path' => 'materials/modul_news_item_xii.pdf',
                        'link_url' => 'https://bbc.co.uk/learningenglish',
                    ],
                ],
            ],

            'Fisika' => [
                10 => [
                    [
                        'title' => 'Besaran, Satuan, dan Pengukuran Presisi',
                        'description' => 'Panduan pengukuran menggunakan jangka sorong, mikrometer sekrup, dan aturan penulisan angka penting.',
                        'file_name' => 'Modul_Pengukuran_Fisika_X.pdf',
                        'file_path' => 'materials/modul_pengukuran_fisika_x.pdf',
                        'link_url' => null,
                    ],
                    [
                        'title' => 'Video Simulasi: Kinematika Gerak Lurus (GLB & GLBB)',
                        'description' => 'Simulasi interaktif grafik posisi-waktu dan kecepatan-waktu pada laboratorium virtual PhET.',
                        'file_name' => null,
                        'file_path' => null,
                        'link_url' => 'https://youtube.com/watch?v=fis_kinematika_x',
                    ],
                ],
                11 => [
                    [
                        'title' => 'Dinamika Rotasi, Torsi, dan Momen Inersia',
                        'description' => 'Bahan kajian gerak menggelinding murni, kesetimbangan benda tegar, dan hukum kekekalan momentum sudut.',
                        'file_name' => 'Slide_Dinamika_Rotasi_XI.pptx',
                        'file_path' => 'materials/slide_dinamika_rotasi_xi.pptx',
                        'link_url' => 'https://drive.google.com/file/d/fisika_rotasi_xi',
                    ],
                ],
                12 => [
                    [
                        'title' => 'Listrik Dinamis, Hukum Kirchhoff & Medan Elektromagnetik',
                        'description' => 'Analisis rangkaian jembatan Wheatstone, arus bolak-balik (AC), dan induksi elektromagnetik Faraday.',
                        'file_name' => 'Modul_Elektromagnetik_XII.pdf',
                        'file_path' => 'materials/modul_elektromagnetik_xii.pdf',
                        'link_url' => 'https://youtube.com/watch?v=fis_listrik_xii',
                    ],
                ],
            ],

            'Kimia' => [
                10 => [
                    [
                        'title' => 'Perkembangan Model Atom dan Konfigurasi Elektron',
                        'description' => 'Modul perbandingan teori atom Dalton hingga mekanika kuantum serta penentuan bilangan kuantum elektron.',
                        'file_name' => 'Modul_Model_Atom_X.pdf',
                        'file_path' => 'materials/modul_model_atom_x.pdf',
                        'link_url' => null,
                    ],
                ],
                11 => [
                    [
                        'title' => 'Termokimia: Penentuan Perubahan Entalpi Reaksi',
                        'description' => 'Penghitungan kalorimeter, aplikasi hukum Hess, dan perhitungan berbasis energi ikatan rata-rata.',
                        'file_name' => 'Modul_Termokimia_Hess_XI.pdf',
                        'file_path' => 'materials/modul_termokimia_hess_xi.pdf',
                        'link_url' => 'https://youtube.com/watch?v=kim_termokimia_xi',
                    ],
                ],
                12 => [
                    [
                        'title' => 'Sifat Koligatif Larutan & Reaksi Redoks Elektrokimia',
                        'description' => 'Konsep penurunan tekanan uap, kenaikan titik didih, sel Volta, dan metode penyetaraan reaksi redoks.',
                        'file_name' => 'Modul_Elektrokimia_XII.pdf',
                        'file_path' => 'materials/modul_elektrokimia_xii.pdf',
                        'link_url' => 'https://example.com/chemistry-redox-slides',
                    ],
                ],
            ],

            'Biologi' => [
                10 => [
                    [
                        'title' => 'Keanekaragaman Hayati & Klasifikasi Makhluk Hidup',
                        'description' => 'Materi tingkat keanekaragaman gen, jenis, dan ekosistem di Indonesia serta kunci determinasi filogeni.',
                        'file_name' => 'Atlas_Biodiversitas_X.pdf',
                        'file_path' => 'materials/atlas_biodiversitas_x.pdf',
                        'link_url' => null,
                    ],
                ],
                11 => [
                    [
                        'title' => 'Struktur Sel dan Jaringan Tumbuhan',
                        'description' => 'Atlas visual mikroskopis jaringan meristem, parenkim, sklerenkim, xilem, dan floem pada organ tumbuhan.',
                        'file_name' => 'Modul_Struktur_Sel_Tumbuhan_XI.pdf',
                        'file_path' => 'materials/modul_struktur_sel_tumbuhan_xi.pdf',
                        'link_url' => 'https://youtube.com/watch?v=bio_sel_tumbuhan_xi',
                    ],
                ],
                12 => [
                    [
                        'title' => 'Substansi Genetika, Sintesis Protein & Pola Hereditas',
                        'description' => 'Struktur rantai heliks ganda DNA/RNA, tahap transkripsi-translasi, dan probabilitas persilangan hukum Mendel.',
                        'file_name' => 'Modul_Genetika_Hereditas_XII.pdf',
                        'file_path' => 'materials/modul_genetika_hereditas_xii.pdf',
                        'link_url' => 'https://nature.com/scitable/topic/genetics',
                    ],
                ],
            ],

            'Sejarah' => [
                10 => [
                    [
                        'title' => 'Konsep Berpikir Sinkronik dan Diakronik dalam Sejarah',
                        'description' => 'Pengantar metodologi penelitian sejarah, verifikasi sumber primer/sekunder, dan kronologi masa praaksara.',
                        'file_name' => 'Modul_Metodologi_Sejarah_X.pdf',
                        'file_path' => 'materials/modul_metodologi_sejarah_x.pdf',
                        'link_url' => null,
                    ],
                ],
                11 => [
                    [
                        'title' => 'Dinamika Pergerakan Nasional dan Transformasi Sosial',
                        'description' => 'Kajian sejarah organisasi Budi Utomo, Sarekat Islam, Indische Partij, serta manifestasi Sumpah Pemuda 1928.',
                        'file_name' => 'Slide_Pergerakan_Nasional_XI.pptx',
                        'file_path' => 'materials/slide_pergerakan_nasional_xi.pptx',
                        'link_url' => 'https://youtube.com/watch?v=sej_pergerakan_xi',
                    ],
                ],
                12 => [
                    [
                        'title' => 'Dinamika Politik Masa Awal Kemerdekaan hingga Orde Reformasi',
                        'description' => 'Perkembangan sistem demokrasi parlementer, kabinet presidensial, dan tantangan integrasi bangsa pasca-proklamasi.',
                        'file_name' => 'Modul_Sejarah_Indonesia_Kontemporer_XII.pdf',
                        'file_path' => 'materials/modul_sejarah_kontemporer_xii.pdf',
                        'link_url' => null,
                    ],
                ],
            ],

            'Ekonomi' => [
                10 => [
                    [
                        'title' => 'Konsep Kelangkaan dan Skala Prioritas Kebutuhan',
                        'description' => 'Analisis opportunity cost, sistem ekonomi pasar vs komando, dan bagan alur kegiatan ekonomi melingkar (circular flow).',
                        'file_name' => 'Modul_Kelangkaan_Ekonomi_X.pdf',
                        'file_path' => 'materials/modul_kelangkaan_ekonomi_x.pdf',
                        'link_url' => null,
                    ],
                ],
                11 => [
                    [
                        'title' => 'Keseimbangan Pasar & Elastisitas Permintaan-Penawaran',
                        'description' => 'Perhitungan matematis fungsi permintaan, penawaran, dan pergeseran kurva ekuilibrium pasar barang pokok.',
                        'file_name' => 'Modul_Keseimbangan_Pasar_XI.pdf',
                        'file_path' => 'materials/modul_keseimbangan_pasar_xi.pdf',
                        'link_url' => 'https://youtube.com/watch?v=eko_pasar_xi',
                    ],
                ],
                12 => [
                    [
                        'title' => 'Siklus Akuntansi Perusahaan Jasa dan Dagang',
                        'description' => 'Pedoman penjurnalan transaksi, posting buku besar, penyesuaian (AJP), dan penyusunan neraca lajur.',
                        'file_name' => 'Buku_Akuntansi_Dasar_XII.pdf',
                        'file_path' => 'materials/buku_akuntansi_dasar_xii.pdf',
                        'link_url' => 'https://kemkeu.go.id/edukasi-akuntansi',
                    ],
                ],
            ],

            'Geografi' => [
                10 => [
                    [
                        'title' => 'Dasar-Dasar Pemetaan dan Penginderaan Jauh',
                        'description' => 'Komponen peta kartografis, kontur interval, proyeksi peta, dan interpretasi foto udara multi-spektral.',
                        'file_name' => 'Panduan_Pemetaan_Geografi_X.pdf',
                        'file_path' => 'materials/panduan_pemetaan_geografi_x.pdf',
                        'link_url' => null,
                    ],
                ],
                11 => [
                    [
                        'title' => 'Sebaran Flora dan Fauna di Wilayah Indonesia dan Dunia',
                        'description' => 'Karakteristik biogeografi garis Wallace, garis Weber, bioma taiga, tundra, dan strategi konservasi alam.',
                        'file_name' => 'Atlas_Biogeografi_XI.pdf',
                        'file_path' => 'materials/atlas_biogeografi_xi.pdf',
                        'link_url' => 'https://youtube.com/watch?v=geo_fauna_xi',
                    ],
                ],
                12 => [
                    [
                        'title' => 'Pola Keruangan Desa-Kota serta Interaksi Spasial Wilayah',
                        'description' => 'Teori konsentris, sektoral, gravitasi Reilly, titik henti Carrothers, dan zonasi tata ruang perkotaan.',
                        'file_name' => 'Modul_Keruangan_Desa_Kota_XII.pdf',
                        'file_path' => 'materials/modul_keruangan_desa_kota_xii.pdf',
                        'link_url' => 'https://big.go.id/portal-spasial',
                    ],
                ],
            ],

            'Pendidikan Jasmani (PJOK)' => [
                10 => [
                    [
                        'title' => 'Komponen Kebugaran Jasmani dan Pola Hidup Sehat',
                        'description' => 'Pedoman pengukuran denyut nadi, indeks massa tubuh (IMT), dan rangkaian latihan interval kebugaran mandiri.',
                        'file_name' => 'Panduan_Kebugaran_Jasmani_X.pdf',
                        'file_path' => 'materials/panduan_kebugaran_jasmani_x.pdf',
                        'link_url' => null,
                    ],
                ],
                11 => [
                    [
                        'title' => 'Taktik dan Formasi Permainan Bola Besar (Voli & Futsal)',
                        'description' => 'Pola penyerangan smash, taktik block ganda, serta formasi transisi bertahan dalam bola voli modern.',
                        'file_name' => 'Modul_Taktik_Bola_Besar_XI.pdf',
                        'file_path' => 'materials/modul_taktik_bola_besar_xi.pdf',
                        'link_url' => 'https://youtube.com/watch?v=pjok_voli_xi',
                    ],
                ],
                12 => [
                    [
                        'title' => 'Perancangan Program Olahraga Rekreatif dan Pertolongan Pertama (P3K)',
                        'description' => 'Penanganan cedera kram, dislokasi, protokol R.I.C.E., dan prinsip keselamatan saat berolahraga di alam terbuka.',
                        'file_name' => 'Panduan_P3K_Olahraga_XII.pdf',
                        'file_path' => 'materials/panduan_p3k_olahraga_xii.pdf',
                        'link_url' => null,
                    ],
                ],
            ],

            'Pendidikan Agama Islam (PAI)' => [
                10 => [
                    [
                        'title' => 'Meneladani Asmaul Husna dalam Kehidupan Sehari-hari',
                        'description' => 'Kajian makna al-Karim, al-Mukmin, al-Wakil, al-Matin, dan al-Adl serta penerapannya dalam akhlak terpuji.',
                        'file_name' => 'Modul_Kajian_Tauhid_PAI_X.pdf',
                        'file_path' => 'materials/modul_kajian_tauhid_pai_x.pdf',
                        'link_url' => null,
                    ],
                ],
                11 => [
                    [
                        'title' => 'Prinsip dan Praktik Ekonomi Syariah (Fiqih Muamalah)',
                        'description' => 'Akad jual beli modern, fintech syariah, asuransi syariah, dan pencegahan praktik spekulasi/riba.',
                        'file_name' => 'Modul_Fiqih_Muamalah_XI.pdf',
                        'file_path' => 'materials/modul_fiqih_muamalah_xi.pdf',
                        'link_url' => 'https://youtube.com/watch?v=pai_muamalah_xi',
                    ],
                ],
                12 => [
                    [
                        'title' => 'Hukum Pernikahan dalam Islam (Munakahat) & Hak Kewajiban Keluarga',
                        'description' => 'Ketentuan rukun nikah, pencegahan pergaulan bebas, dan prinsip sakinah mawaddah wa rahmah.',
                        'file_name' => 'Buku_Panduan_Munakahat_XII.pdf',
                        'file_path' => 'materials/buku_panduan_munakahat_xii.pdf',
                        'link_url' => null,
                    ],
                ],
            ],

            'Seni Budaya' => [
                10 => [
                    [
                        'title' => 'Unsur dan Prinsip Dasar Seni Rupa Dua Dimensi',
                        'description' => 'Studi garis, bidang, gradasi warna, tekstur, ruang visual, serta harmoni proporsi karya visual ragam hias.',
                        'file_name' => 'Buku_Seni_Rupa_Dasar_X.pdf',
                        'file_path' => 'materials/buku_seni_rupa_dasar_x.pdf',
                        'link_url' => null,
                    ],
                ],
                11 => [
                    [
                        'title' => 'Eksplorasi Musik Tradisional Nusantara dan Tangga Nada',
                        'description' => 'Fungsi musik tradisional dalam upacara adat, sistem laras slendro-pelog, dan teknik tabuhan ensambel gamelan.',
                        'file_name' => 'Modul_Musik_Tradisional_XI.pdf',
                        'file_path' => 'materials/modul_musik_tradisional_xi.pdf',
                        'link_url' => 'https://youtube.com/watch?v=seni_gamelan_xi',
                    ],
                ],
                12 => [
                    [
                        'title' => 'Manajemen Pameran Karya Seni Rupa & Kurasi Galeri Sekolah',
                        'description' => 'Tata kelola kepanitiaan pameran, pembuatan katalog pameran, penataan tata cahaya, dan buku kritik seni.',
                        'file_name' => 'Panduan_Pameran_Seni_XII.pdf',
                        'file_path' => 'materials/panduan_pameran_seni_xii.pdf',
                        'link_url' => 'https://galeri-nasional.or.id/edukasi',
                    ],
                ],
            ],

            'Prakarya' => [
                10 => [
                    [
                        'title' => 'Kewirausahaan Produk Kerajinan dari Bahan Limbah Organik',
                        'description' => 'Analisis potensi bahan pelepah pisang, eceng gondok, dan limbah kayu untuk produk kriya bernilai jual tinggi.',
                        'file_name' => 'Panduan_Kerajinan_Limbah_X.pdf',
                        'file_path' => 'materials/panduan_kerajinan_limbah_x.pdf',
                        'link_url' => null,
                    ],
                ],
                11 => [
                    [
                        'title' => 'Pengolahan dan Pengemasan Makanan Khas Daerah Nusantara',
                        'description' => 'Proses pengawetan higienis, pengemasan vakum modern, perhitungan HPP, dan strategi branding produk kuliner.',
                        'file_name' => 'Modul_Pengolahan_Kuliner_XI.pdf',
                        'file_path' => 'materials/modul_pengolahan_kuliner_xi.pdf',
                        'link_url' => 'https://youtube.com/watch?v=prakarya_kuliner_xi',
                    ],
                ],
                12 => [
                    [
                        'title' => 'Rekayasa Produk Teknologi Tepat Guna Ramah Lingkungan',
                        'description' => 'Perancangan prototipe alat filter air sederhana, komposter biopori otomatis, dan uji kelayakan fungsional.',
                        'file_name' => 'Proposal_Teknologi_Tepat_Guna_XII.pdf',
                        'file_path' => 'materials/proposal_teknologi_tepat_guna_xii.pdf',
                        'link_url' => 'https://ristekbrin.go.id/teknologi-tepat-guna',
                    ],
                ],
            ],
        ];

        $totalMaterialsCreated = 0;

        foreach ($teachers as $teacher) {
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

            // Ambil seluruh kelas yang diajar guru bersangkutan
            $schedules = Schedule::where('teacher_id', $teacher->id)->with('schoolClass')->get();
            $classesTaught = $schedules->pluck('schoolClass')->filter()->unique('id')->values();

            if ($classesTaught->isEmpty() && $teacher->waliClass) {
                $classesTaught = collect([$teacher->waliClass]);
            }
            if ($classesTaught->isEmpty()) {
                $classesTaught = $classes;
            }

            // Loop untuk setiap kelas yang diajar guru ini
            foreach ($classesTaught as $class) {
                $gradeLevel = (int) $class->grade_level;
                if (!in_array($gradeLevel, [10, 11, 12])) {
                    $gradeLevel = 10;
                }

                // Dapatkan template materi untuk mapel dan jenjang ini
                $materialsList = $curatedMaterials[$subjectName][$gradeLevel] ?? null;

                if (!$materialsList) {
                    $materialsList = [
                        [
                            'title' => "Modul Pembelajaran {$subjectName} - Kelas {$class->name}",
                            'description' => "Silakan pelajari bahan ajar pokok {$subjectName} berikut sebelum pertemuan tatap muka.",
                            'file_name' => "Modul_{$subjectSlug}_{$class->name}.pdf",
                            'file_path' => "materials/modul_{$subjectSlug}.pdf",
                            'link_url' => null,
                        ]
                    ];
                }

                foreach ($materialsList as $item) {
                    $createdAt = Carbon::now()->subDays(rand(2, 10))->subHours(rand(1, 12));

                    Material::create([
                        'teacher_id' => $teacher->id,
                        'school_class_id' => $class->id,
                        'subject_id' => $subject->id,
                        'title' => $item['title'],
                        'description' => $item['description'],
                        'file_path' => $item['file_path'],
                        'file_name' => $item['file_name'],
                        'link_url' => $item['link_url'],
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);

                    $totalMaterialsCreated++;
                }
            }
        }

        if ($this->command) {
            $this->command->info("MaterialSeeder: Berhasil membuat {$totalMaterialsCreated} data materi belajar untuk " . $teachers->count() . " guru di seluruh kelas.");
        }
    }
}
