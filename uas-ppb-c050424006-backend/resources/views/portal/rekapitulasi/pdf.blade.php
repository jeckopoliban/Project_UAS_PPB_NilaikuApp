<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Nilai - {{ $namaSemester }}</title>
    <style>
        @page {
            margin: 0;
        }
        body {
            font-family: 'Arial', sans-serif;
            color: #111827;
            margin: 0;
            padding: 2.54cm;
            line-height: 1.4;
        }
        .page {
            width: 100%;
            box-sizing: border-box;
        }
        .branding {
            text-align: left;
            margin-bottom: 8px;
        }
        .branding .brand-name {
            margin: 0;
            font-size: 14pt;
            font-weight: bold;
            color: #1a1a1a;
            line-height: 1.2;
        }
        .branding .brand-subtitle {
            margin: 4px 0 0;
            font-size: 11pt;
            color: #6b7280;
            line-height: 1.3;
        }
        .header {
            text-align: center;
            margin-bottom: 24px;
        }
        .details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .details .block {
            width: 48%;
            min-width: 240px;
        }
        .details .field {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .details .field .label {
            width: 110px;
            font-size: 11px;
            color: #4b5563;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            flex-shrink: 0;
        }
        .details .field .value {
            font-size: 13px;
            color: #111827;
            text-align: left;
            width: calc(100% - 110px);
        }
        .details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .details .block {
            width: 48%;
            min-width: 240px;
        }
        .details .field {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .details .field .label {
            width: 120px;
            font-size: 11px;
            color: #4b5563;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            flex-shrink: 0;
        }
        .details .field .value {
            font-size: 13px;
            color: #111827;
            text-align: left;
            width: calc(100% - 120px);
        }
        .header h1 {
            font-size: 18px;
            margin: 0;
            letter-spacing: 1px;
        }
        .header p {
            margin: 4px 0 0;
            font-size: 11px;
            color: #4b5563;
        }
        .decorative-wave {
            width: 100%;
            height: 40px;
            margin-bottom: 18px;
        }
        .details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .details .block {
            width: 48%;
            min-width: 240px;
        }
        .details .block strong {
            display: block;
            margin-bottom: 4px;
            font-size: 11px;
            color: #4b5563;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        .details .block span {
            display: block;
            font-size: 13px;
            color: #111827;
        }
        .report-title {
            margin: 0 0 24px;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #1f2937;
        }
        .table-wrapper {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        .table-wrapper th,
        .table-wrapper td {
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            font-size: 12px;
            vertical-align: middle;
        }
        .table-wrapper th {
            background: #f3f4f6;
            color: #111827;
            text-align: left;
            font-weight: 700;
        }
        .table-wrapper tbody tr:nth-child(even) {
            background: #fafafa;
        }
        .table-summary {
            width: 100%;
            margin-top: 12px;
            border-collapse: collapse;
        }
        .table-summary td {
            padding: 8px 12px;
            font-size: 12px;
            border: 1px solid #e5e7eb;
        }
        .summary-label {
            color: #374151;
        }
        .summary-value {
            font-weight: 700;
            text-align: right;
        }
        .footer {
            margin-top: 28px;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        .footer .note {
            font-size: 11px;
            color: #6b7280;
            width: 60%;
            min-width: 280px;
        }
        .footer .signature {
            text-align: right;
            min-width: 200px;
        }
        .footer .signature p {
            margin: 0;
            font-size: 12px;
        }
        .footer .signature .name {
            margin-top: 8px;
            font-weight: 700;
            font-size: 12px;
        }
        .signature p {
            margin: 0;
            font-size: 12px;
        }
        .signature .name {
            margin-top: 42px;
            font-weight: 700;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="branding">
            <p class="brand-name">NILAIKU APP</p>
            <p class="brand-subtitle">Sistem Pencatatan Nilai Pribadi Mahasiswa</p>
        </div>
        <div class="header">
            <h1>REKAPITULASI NILAI AKADEMIK</h1>
            <p>Laporan Hasil Studi (Kartu Hasil Studi)</p>
        </div>

        <svg class="decorative-wave" viewBox="0 0 1200 40" preserveAspectRatio="none">
            <path d="M0,40 C300,0 900,80 1200,40 L1200,0 L0,0 Z" fill="#1d4ed8" opacity="0.18" />
        </svg>

        <div class="details">
            <div class="block">
                <div class="field">
                    <span class="label">Nama</span>
                    <span class="value">{{ $user->name ?? '-' }}</span>
                </div>
                <div class="field">
                    <span class="label">NIM / NIS</span>
                    <span class="value">{{ $profil->nim_nis ?? '-' }}</span>
                </div>
            </div>
            <div class="block">
                <div class="field">
                    <span class="label">Program Studi</span>
                    <span class="value">{{ $profil->program_studi ?? '-' }}</span>
                </div>
                <div class="field">
                    <span class="label">Semester</span>
                    <span class="value">{{ $namaSemester }}</span>
                </div>
            </div>
        </div>

        <p class="report-title">Ringkasan Mata Kuliah</p>

        <table class="table-wrapper">
            <thead>
                <tr>
                    <th>Nama Mata Kuliah</th>
                    <th>SKS</th>
                    <th>Nilai Akhir</th>
                    <th>Huruf Mutu</th>
                    <th>Indeks</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tableRows as $row)
                    <tr>
                        <td>{{ $row['nama_mk'] }}</td>
                        <td>{{ $row['sks'] }}</td>
                        <td>{{ $row['nilai_akhir'] !== null ? number_format($row['nilai_akhir'], 2) : '-' }}</td>
                        <td>{{ $row['huruf_mutu'] ?? '-' }}</td>
                        <td>{{ $row['indeks'] !== null ? number_format($row['indeks'], 2) : '-' }}</td>
                        <td>{{ $row['status'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="table-summary">
            <tr>
                <td class="summary-label">Total SKS Semester</td>
                <td class="summary-value">{{ $summary['total_sks'] }}</td>
            </tr>
            <tr>
                <td class="summary-label">Jumlah Mata Kuliah</td>
                <td class="summary-value">{{ $summary['total_mata_kuliah'] }}</td>
            </tr>
            <tr>
                <td class="summary-label">IP Semester</td>
                <td class="summary-value">{{ $summary['ip_semester'] !== null ? number_format($summary['ip_semester'], 2) : '-' }}</td>
            </tr>
            <tr>
                <td class="summary-label">Bobot Mutu Total</td>
                <td class="summary-value">{{ number_format($summary['total_bobot_mutu'], 2) }}</td>
            </tr>
        </table>

        <div class="footer">
            <div class="note">
                <p>Dokumen ini dihasilkan otomatis sebagai ringkasan hasil studi per semester. Pastikan semua data nilai dan komponen telah diperbarui sebelum dicetak.</p>
            </div>
            <div class="signature">
                <p>Tanggal Cetak</p>
                <p class="name">{{ $tanggalCetak }}</p>
            </div>
        </div>
    </div>
</body>
</html>
