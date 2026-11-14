<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Pengumuman Akademik - SMKN 1 Ciomas</title>
    <link rel="icon" type="image/png" href="/images/logo-skanic.png">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            color: #111827;
            background-color: #f3f4f6;
            line-height: 1.4;
        }

        /* Toolbar Layar (Tidak ikut tercetak) */
        .no-print-toolbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #1e293b;
            color: #ffffff;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
            z-index: 9999;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .toolbar-title {
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .toolbar-badge {
            background-color: #3b82f6;
            color: #fff;
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 9999px;
            font-weight: 600;
        }

        .toolbar-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            font-size: 13px;
            font-weight: 600;
            padding: 6px 16px;
            border-radius: 6px;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s ease;
            text-decoration: none;
        }

        .btn-print {
            background-color: #2563eb;
            color: white;
        }
        .btn-print:hover {
            background-color: #1d4ed8;
        }

        .btn-close {
            background-color: #475569;
            color: white;
        }
        .btn-close:hover {
            background-color: #334155;
        }

        /* Lembar Kertas A4 */
        .sheet {
            background: #ffffff;
            width: 210mm;
            min-height: 297mm;
            margin: 64px auto 30px auto;
            padding: 18mm 20mm 20mm 20mm;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        /* Kop Surat Resmi SMKN 1 Ciomas */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }

        .kop-logo {
            width: 90px;
            vertical-align: middle;
            text-align: center;
            padding-right: 15px;
        }

        .kop-logo img {
            width: 82px;
            height: auto;
            display: block;
        }

        .kop-text {
            text-align: center;
            vertical-align: middle;
        }

        .kop-text h4 {
            font-size: 13pt;
            font-weight: normal;
            letter-spacing: 0.5px;
            margin: 0;
            text-transform: uppercase;
        }

        .kop-text h3 {
            font-size: 14pt;
            font-weight: bold;
            margin: 0;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .kop-text h2 {
            font-size: 17pt;
            font-weight: 800;
            margin: 1px 0;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .kop-text p.address {
            font-size: 9.5pt;
            font-weight: normal;
            margin-top: 3px;
            color: #1f2937;
            line-height: 1.3;
        }

        .kop-text p.contact {
            font-size: 9pt;
            font-weight: normal;
            color: #1f2937;
        }

        .kop-divider {
            margin-top: 6px;
            margin-bottom: 16px;
        }

        .kop-line-thick {
            border-top: 3px solid #000;
        }

        .kop-line-thin {
            border-top: 1px solid #000;
            margin-top: 2px;
        }

        /* Judul Dokumen */
        .report-header {
            text-align: center;
            margin-bottom: 16px;
        }

        .report-header h1 {
            font-size: 14pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .report-header p {
            font-size: 10pt;
            margin-top: 2px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Informasi Rekapitulasi & Filter */
        .meta-box {
            width: 100%;
            margin-bottom: 14px;
            font-size: 9.5pt;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-table td {
            padding: 2px 4px;
            vertical-align: top;
        }

        .meta-label {
            width: 130px;
            font-weight: bold;
            color: #374151;
        }

        .meta-colon {
            width: 10px;
        }

        /* Tabel Rekap */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5pt;
            margin-bottom: 24px;
        }

        .data-table th, 
        .data-table td {
            border: 1px solid #374151;
            padding: 6px 8px;
        }

        .data-table th {
            background-color: #f3f4f6;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            font-size: 9pt;
            letter-spacing: 0.3px;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }

        .badge-target {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8.5pt;
            font-weight: bold;
        }

        .badge-all {
            background-color: #f3e8ff;
            color: #6b21a8;
            border: 1px solid #d8b4fe;
        }

        .badge-guru {
            background-color: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }

        .badge-siswa {
            background-color: #fff7ed;
            color: #c2410c;
            border: 1px solid #fed7aa;
        }

        /* Tanda Tangan */
        .signature-section {
            width: 100%;
            margin-top: 24px;
            page-break-inside: avoid;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }

        .signature-table td {
            vertical-align: top;
            font-size: 10pt;
        }

        .sig-right {
            width: 45%;
            text-align: center;
        }

        .sig-space {
            height: 70px;
        }

        .sig-name {
            font-weight: bold;
            text-decoration: underline;
        }

        .footer-note {
            margin-top: 30px;
            padding-top: 8px;
            border-top: 1px dashed #9ca3af;
            font-size: 8pt;
            color: #6b7280;
            display: flex;
            justify-content: space-between;
        }

        /* Pengaturan Cetak A4 */
        @page {
            size: A4 portrait;
            margin: 15mm 15mm 15mm 15mm;
        }

        @media print {
            .no-print-toolbar {
                display: none !important;
            }

            body {
                background: #ffffff;
                color: #000000;
            }

            .sheet {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
            }

            .data-table th {
                background-color: #eaeaea !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .badge-target {
                border: 1px solid #888 !important;
                background: transparent !important;
                color: #000 !important;
            }
        }
    </style>
</head>
<body>

    <!-- Toolbar Atas (Tampilan Layar Browser) -->
    <div class="no-print-toolbar">
        <div class="toolbar-title">
            <span>Rekapitulasi Pengumuman Akademik SMKN 1 Ciomas</span>
            <span class="toolbar-badge">{{ count($announcements) }} Data</span>
        </div>
        <div class="toolbar-actions">
            <button onclick="window.print()" class="btn btn-print">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4"></path>
                </svg>
                Cetak / Simpan PDF
            </button>
            <button onclick="window.close()" class="btn btn-close">
                Tutup
            </button>
        </div>
    </div>

    <!-- Lembar Cetak A4 -->
    <div class="sheet">
        
        <!-- KOP SURAT RESMI SMKN 1 CIOMAS -->
        <table class="kop-table">
            <tr>
                <td class="kop-logo">
                    <img src="/images/logo-skanic.png" alt="Logo SMKN 1 Ciomas">
                </td>
                <td class="kop-text">
                    <h4>Pemerintah Daerah Provinsi Jawa Barat</h4>
                    <h3>Dinas Pendidikan</h3>
                    <h4>Cabang Dinas Pendidikan Wilayah I</h4>
                    <h2>SMK NEGERI 1 CIOMAS</h2>
                    <p class="address">Jl. Raya Laladon No. 1, Desa Laladon, Kec. Ciomas, Kab. Bogor, Jawa Barat 16610</p>
                    <p class="contact">Telepon: (0251) 7520120 | Laman: https://smkn1ciomas.sch.id | Pos-el: smkn1ciomas@gmail.com</p>
                </td>
            </tr>
        </table>

        <div class="kop-divider">
            <div class="kop-line-thick"></div>
            <div class="kop-line-thin"></div>
        </div>

        <!-- JUDUL LAPORAN -->
        <div class="report-header">
            <h1>LAPORAN REKAPITULASI PENGUMUMAN AKADEMIK</h1>
            <p>PORTAL AKADEMIK RESMI SMKN 1 CIOMAS</p>
        </div>

        <!-- INFORMASI REKAP & FILTER AKTIF -->
        <div class="meta-box">
            <table class="meta-table">
                <tr>
                    <td class="meta-label">Periode Tanggal</td>
                    <td class="meta-colon">:</td>
                    <td>
                        @if(!empty($filters['start_date']) && !empty($filters['end_date']))
                            {{ \Carbon\Carbon::parse($filters['start_date'])->locale('id')->isoFormat('D MMMM Y') }} s/d {{ \Carbon\Carbon::parse($filters['end_date'])->locale('id')->isoFormat('D MMMM Y') }}
                        @elseif(!empty($filters['start_date']))
                            Mulai dari {{ \Carbon\Carbon::parse($filters['start_date'])->locale('id')->isoFormat('D MMMM Y') }}
                        @elseif(!empty($filters['end_date']))
                            Sampai dengan {{ \Carbon\Carbon::parse($filters['end_date'])->locale('id')->isoFormat('D MMMM Y') }}
                        @else
                            Semua Waktu (Seluruh Periode)
                        @endif
                    </td>
                    <td class="meta-label">Dicetak Pada</td>
                    <td class="meta-colon">:</td>
                    <td>{{ \Carbon\Carbon::parse($printedAt)->locale('id')->isoFormat('D MMMM Y, HH:mm') }} WIB</td>
                </tr>
                <tr>
                    <td class="meta-label">Filter Sasaran</td>
                    <td class="meta-colon">:</td>
                    <td>
                        @if(($filters['target_role'] ?? '') === 'guru')
                            Khusus Guru
                        @elseif(($filters['target_role'] ?? '') === 'siswa')
                            Khusus Siswa
                        @elseif(($filters['target_role'] ?? '') === 'all')
                            Semua Pengguna (Guru & Siswa)
                        @else
                            Semua Sasaran
                        @endif
                    </td>
                    <td class="meta-label">Dicetak Oleh</td>
                    <td class="meta-colon">:</td>
                    <td>{{ $printedBy }}</td>
                </tr>
                <tr>
                    <td class="meta-label">Kata Kunci</td>
                    <td class="meta-colon">:</td>
                    <td>{{ !empty($filters['search']) ? '"'.$filters['search'].'"' : 'Semua Pencarian' }}</td>
                    <td class="meta-label">Total Pengumuman</td>
                    <td class="meta-colon">:</td>
                    <td><strong>{{ count($announcements) }} Dokumen</strong></td>
                </tr>
            </table>
        </div>

        <!-- TABEL DATA REKAPITULASI -->
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No.</th>
                    <th style="width: 38%;">Judul Pengumuman</th>
                    <th style="width: 15%;">Sasaran</th>
                    <th style="width: 17%;">Tanggal Terbit</th>
                    <th style="width: 15%;">Pembuat</th>
                    <th style="width: 10%;">Pembaca</th>
                </tr>
            </thead>
            <tbody>
                @forelse($announcements as $index => $announcement)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $announcement->title }}</strong>
                            <div style="font-size: 8pt; color: #4b5563; margin-top: 2px;">
                                {{ Str::limit(strip_tags($announcement->content), 85) }}
                            </div>
                        </td>
                        <td class="text-center">
                            @if($announcement->target_role === 'guru')
                                <span class="badge-target badge-guru">Khusus Guru</span>
                            @elseif($announcement->target_role === 'siswa')
                                <span class="badge-target badge-siswa">Khusus Siswa</span>
                            @else
                                <span class="badge-target badge-all">Semua Pengguna</span>
                            @endif
                        </td>
                        <td class="text-center">
                            {{ \Carbon\Carbon::parse($announcement->created_at)->locale('id')->isoFormat('D MMM Y') }}
                            <div style="font-size: 8pt; color: #6b7280;">
                                {{ \Carbon\Carbon::parse($announcement->created_at)->format('H:i') }} WIB
                            </div>
                        </td>
                        <td class="text-center">
                            {{ $announcement->user->name ?? 'Admin Portal' }}
                        </td>
                        <td class="text-center">
                            <strong>{{ $announcement->readers_count ?? 0 }}</strong>
                            <span style="font-size: 8pt; color: #6b7280;">user</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 24px; color: #6b7280; font-style: italic;">
                            Tidak ada data pengumuman yang sesuai dengan filter pencarian yang diterapkan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- BAGIAN TANDA TANGAN (KANAN BAWAH) -->
        <div class="signature-section">
            <table class="signature-table">
                <tr>
                    <td style="width: 55%;"></td>
                    <td class="sig-right">
                        <p>Ciomas, {{ \Carbon\Carbon::parse($printedAt)->locale('id')->isoFormat('D MMMM Y') }}</p>
                        <p>Mengetahui,</p>
                        <p><strong>Kepala SMKN 1 Ciomas</strong></p>
                        <div class="sig-space"></div>
                        <p class="sig-name">Drs. H. Mahdi, M.Pd.</p>
                        <p>NIP. 19680512 199403 1 004</p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- FOOTER CATATAN DOKUMEN -->
        <div class="footer-note">
            <span>Dicetak secara otomatis dari Portal Akademik SMKN 1 Ciomas</span>
            <span>Halaman 1 dari 1</span>
        </div>

    </div>

    <!-- Otomatis pemicu print dialog -->
    <script>
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 600);
        });
    </script>
</body>
</html>

