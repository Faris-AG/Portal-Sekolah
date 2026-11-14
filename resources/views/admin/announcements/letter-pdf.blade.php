<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Edaran - {{ $announcement->title }}</title>
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
            line-height: 1.6;
        }

        /* Toolbar Layar Browser */
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
            padding: 20mm 25mm 20mm 25mm;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        /* Kop Surat Resmi */
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
            margin-bottom: 20px;
        }

        .kop-line-thick {
            border-top: 3px solid #000;
        }

        .kop-line-thin {
            border-top: 1px solid #000;
            margin-top: 2px;
        }

        /* Informasi Surat (Nomor & Tanggal) */
        .letter-meta {
            width: 100%;
            margin-bottom: 18px;
            font-size: 11pt;
        }

        .letter-meta table {
            width: 100%;
            border-collapse: collapse;
        }

        .letter-meta td {
            vertical-align: top;
            padding: 1px 0;
        }

        /* Tujuan Surat */
        .recipient-box {
            margin-bottom: 22px;
            font-size: 11pt;
            line-height: 1.4;
        }

        .recipient-box .to-label {
            font-weight: normal;
        }

        .recipient-box .to-name {
            font-weight: bold;
            margin-top: 2px;
        }

        /* Judul Surat Edaran */
        .letter-title {
            text-align: center;
            margin: 24px 0 20px 0;
        }

        .letter-title h1 {
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0;
        }

        .letter-title h2 {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 4px;
            letter-spacing: 0.5px;
        }

        /* Isi Pengumuman */
        .letter-body {
            font-size: 11pt;
            text-align: justify;
            line-height: 1.7;
        }

        .letter-body p {
            margin-bottom: 14px;
            text-indent: 2.5em;
        }

        .content-block {
            margin: 16px 0;
            padding: 0 4px;
            white-space: pre-line;
            text-indent: 0;
            line-height: 1.75;
        }

        /* Bagian Pengesahan / Tanda Tangan */
        .signature-section {
            width: 100%;
            margin-top: 36px;
            page-break-inside: avoid;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }

        .signature-table td {
            vertical-align: top;
            font-size: 11pt;
        }

        .sig-right {
            width: 48%;
            text-align: center;
            line-height: 1.4;
        }

        .sig-space {
            height: 75px;
        }

        .sig-name {
            font-weight: bold;
            text-decoration: underline;
        }

        /* Tembusan */
        .tembusan {
            margin-top: 20px;
            font-size: 9.5pt;
            color: #374151;
            line-height: 1.35;
        }

        .tembusan strong {
            font-size: 10pt;
        }

        .tembusan ol {
            margin-left: 18px;
            margin-top: 2px;
        }

        /* Footer Nota */
        .doc-watermark {
            margin-top: 30px;
            padding-top: 8px;
            border-top: 1px solid #d1d5db;
            font-size: 8pt;
            color: #6b7280;
            display: flex;
            justify-content: space-between;
        }

        /* Print CSS A4 */
        @page {
            size: A4 portrait;
            margin: 15mm 20mm 15mm 20mm;
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
        }
    </style>
</head>
<body>

    <!-- Toolbar Atas (Layar Browser) -->
    <div class="no-print-toolbar">
        <div class="toolbar-title">
            <span>Surat Edaran Resmi SMKN 1 Ciomas</span>
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

        <!-- NOMOR & TANGGAL SURAT -->
        <div class="letter-meta">
            <table>
                <tr>
                    <td style="width: 80px;">Nomor</td>
                    <td style="width: 15px;">:</td>
                    <td>421.5 / {{ str_pad($announcement->id, 3, '0', STR_PAD_LEFT) }} / SMKN1-CMS / {{ \Carbon\Carbon::parse($announcement->created_at)->format('Y') }}</td>
                    <td style="text-align: right; width: 200px;">
                        Ciomas, {{ \Carbon\Carbon::parse($announcement->created_at)->locale('id')->isoFormat('D MMMM Y') }}
                    </td>
                </tr>
                <tr>
                    <td>Lampiran</td>
                    <td>:</td>
                    <td>-</td>
                    <td></td>
                </tr>
                <tr>
                    <td>Perihal</td>
                    <td>:</td>
                    <td><strong>Pemberitahuan / Edaran Resmi Sekolah</strong></td>
                    <td></td>
                </tr>
            </table>
        </div>

        <!-- TUJUAN SURAT -->
        <div class="recipient-box">
            <div class="to-label">Kepada Yth.</div>
            <div class="to-name">
                @if($announcement->target_role === 'guru')
                    Bapak / Ibu Guru dan Tenaga Kependidikan
                @elseif($announcement->target_role === 'siswa')
                    Seluruh Siswa / Siswi (Peserta Didik)
                @else
                    Seluruh Civitas Akademika (Bapak/Ibu Guru & Peserta Didik)
                @endif
            </div>
            <div>SMK Negeri 1 Ciomas</div>
            <div>di Tempat</div>
        </div>

        <!-- JUDUL SURAT EDARAN -->
        <div class="letter-title">
            <h1>SURAT EDARAN</h1>
            <h2>TENTANG: {{ strtoupper($announcement->title) }}</h2>
        </div>

        <!-- ISI SURAT EDARAN -->
        <div class="letter-body">
            <p>
                Dengan hormat, sehubungan dengan kebijakan dan agenda kegiatan pembelajaran di lingkungan SMK Negeri 1 Ciomas, disampaikan kepada seluruh
                @if($announcement->target_role === 'guru')
                    Bapak/Ibu Guru dan Tenaga Kependidikan
                @elseif($announcement->target_role === 'siswa')
                    Siswa dan Siswi SMKN 1 Ciomas
                @else
                    Bapak/Ibu Guru serta Siswa/i SMKN 1 Ciomas
                @endif
                mengenai hal penting sebagai berikut:
            </p>

            <div class="content-block">{!! nl2br(e($announcement->content)) !!}</div>

            <p>
                Demikian surat edaran ini disampaikan agar dapat diketahui, dipedomani, dan dilaksanakan dengan penuh rasa tanggung jawab. Atas perhatian dan kerjasama yang baik dari Bapak/Ibu serta seluruh peserta didik, kami ucapkan terima kasih.
            </p>
        </div>

        <!-- BLOK PENGESAHAN / TANDA TANGAN (KANAN BAWAH) -->
        <div class="signature-section">
            <table class="signature-table">
                <tr>
                    <td style="width: 52%;">
                        <!-- Tembusan Surat -->
                        <div class="tembusan">
                            <strong>Tembusan Yth:</strong>
                            <ol>
                                <li>Kepala Cabang Dinas Pendidikan Wil. I Prov. Jawa Barat</li>
                                <li>Pengawas Pembina SMKN 1 Ciomas</li>
                                <li>Komite Sekolah SMKN 1 Ciomas</li>
                                <li>Arsip / Dokumen Akademik</li>
                            </ol>
                        </div>
                    </td>
                    <td class="sig-right">
                        <p>Mengetahui & Mengesahkan,</p>
                        <p><strong>Kepala SMKN 1 Ciomas</strong></p>
                        <div class="sig-space"></div>
                        <p class="sig-name">Drs. H. Mahdi, M.Pd.</p>
                        <p>NIP. 19680512 199403 1 004</p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- FOOTER INTEGRASI SISTEM PORTAL -->
        <div class="doc-watermark">
            <span>Diterbitkan via Portal Akademik SMKN 1 Ciomas (ID: #{{ $announcement->id }}) | Pembuat: {{ $announcement->user->name ?? 'Administrator' }}</span>
            <span>Tercatat Dibaca: {{ $announcement->readers_count ?? 0 }} Pengguna</span>
        </div>

    </div>

    <!-- Pemicu Cetak Otomatis -->
    <script>
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 600);
        });
    </script>
</body>
</html>

