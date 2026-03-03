<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Tracer Study</title>

    <!-- Bootstrap 5 -->
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"> -->

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            margin: 0;
        }

        /* ================= HEADER / KOP SURAT ================= */

        .kop-container {
            width: 100%;
            margin-bottom: 10px;
        }

        .kop-table {
            width: 100%;
            border-collapse: collapse;
        }

        .kop-table td {
            border: none;
            vertical-align: middle;
        }

        .logo {
            width: 90px;
        }

        .kop-text {
            text-align: center;
            transform: translateX(-77px);
        }

        .kop-text h2 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }

        .kop-text p {
            margin: 2px 0;
            font-size: 11px;
        }

        .line {
            border-top: 3px solid #000;
            margin-top: 8px;
        }

        .line-thin {
            border-top: 1px solid #000;
            margin-top: 2px;
            margin-bottom: 15px;
        }

        /* ================= JUDUL ================= */

        .report-title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .report-subtitle {
            text-align: center;
            font-size: 11px;
            margin-bottom: 15px;
        }

        /* ================= TABLE ================= */

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }

        table th, table td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: top;
        }

        table th {
            background: #f2f2f2;
            text-align: left;
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .cap {
            text-transform: capitalize;
        }

        /* ================= FOOTER / TTD ================= */

        .signature-container {
            width: 100%;
            margin-top: 40px;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }

        .signature-table td {
            border: none;
            text-align: center;
            vertical-align: top;
        }

        .signature-space {
            height: 60px;
        }

        .bold {
            font-weight: bold;
        }

    </style>
</head>
<body>

    {{-- ================= KOP SURAT ================= --}}
    <div class="kop-container">
        <table class="kop-table">
            <tr>
                <td width="15%">
                    {{-- Ganti dengan path logo kampus --}}
                    <img src="{{ public_path('logo.png') }}" class="logo">
                </td>
                <td width="85%" class="kop-text">
                    <h2>UNIVERSITAS CONTOH INDONESIA</h2>
                    <p>Fakultas Ilmu Komputer</p>
                    <p>Jl. Pendidikan No. 123, Kota Contoh, Indonesia</p>
                    <p>Telp: (021) 12345678 | Email: info@universitas.ac.id</p>
                </td>
            </tr>
        </table>

        <div class="line"></div>
        <div class="line-thin"></div>
    </div>

    {{-- ================= JUDUL LAPORAN ================= --}}
    <div class="report-title">
        LAPORAN TRACER STUDY
    </div>

    <div class="report-subtitle">
        Total Data: {{ count($data) }}
    </div>

    {{-- ================= TABEL DATA ================= --}}
    <table>
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="12%">Nama</th>
                <th width="8%">NIM</th>
                <th width="12%">Program Studi</th>
                <th width="8%">Tahun Masuk</th>
                <th width="8%">Tahun Lulus</th>
                <th width="8%">Status karir</th>
                <th width="8%">Relevansi Studi</th>
                <th width="21%">Info Karir</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $row)
                <tr>
                    <td class="text-center">
                        {{ $index + 1 }}
                    </td>

                    <td>
                        {{ $row->user->name ?? '-' }}
                    </td>

                    <td>
                        {{ $row->student_id_number ?? '-' }}
                    </td>

                    <td>
                        {{ $row->studyProgram->name ?? '-' }}
                    </td>

                    <td>
                        {{ $row->entry_year ?? '-' }}
                    </td>

                    <td>
                        {{ $row->graduation_year ?? '-' }}
                    </td>

                    <td class="cap">
                        {{ $row->employment_status ?? '-' }}
                    </td>

                    <td class="cap">
                        {{ $row->job_study_relevance_level ?? '-' }}
                    </td>

                    <td>
                        <strong>Tempat:</strong> {{ $row->current_workplace ?? '-' }} <br>
                        <strong>Jabatan:</strong> {{ $row->job_title ?? '-' }} <br>
                        <strong>Jenis:</strong> {{ $row->job_category ?? '-' }} <br>
                        <strong>Tipe:</strong> {{ $row->employment_type ?? '-' }} <br>
                        <strong>Sektor:</strong> {{ $row->employment_sector ?? '-' }} <br>
                        <strong>Gaji:</strong> {{ $row->monthly_income_range ?? '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ================= TANDA TANGAN ================= --}}
    <div class="signature-container">
        <table class="signature-table">
            <tr>
                <td width="60%"></td>
                <td width="40%">
                    Kendal, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    Kepala Bagian Akademik
                </td>
            </tr>
            <tr>
                <td></td>
                <td class="signature-space"></td>
            </tr>
            <tr>
                <td></td>
                <td class="bold">
                    (_________________________)
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
