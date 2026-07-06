@extends('portal.layout')

@section('title', 'Rekapitulasi Nilai')
@section('header', 'Rekapitulasi Nilai / Hasil Studi')

@section('content')
<div class="rounded-app-card border border-border-subtle bg-white p-6 shadow-app-soft max-w-6xl mx-auto">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-text-heading">Rekapitulasi Nilai</h2>
            <p class="text-sm text-text-label">Lihat ringkasan hasil studi per semester dan cetak laporan Anda.</p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <form method="GET" action="{{ route('portal.rekapitulasi') }}" class="flex items-center gap-3">
                <select name="semester" onchange="this.form.submit()" class="rounded-app-pill border border-border-subtle bg-slate-50 px-4 py-3 text-sm text-slate-900 focus:border-brand-blue focus:ring-brand-blue/50 focus:outline-none">
                    @foreach ($tahunAkademiks as $semester)
                        <option value="{{ $semester->id }}" {{ $semester->id == $selectedSemesterId ? 'selected' : '' }}>{{ $semester->nama }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-app-pill bg-gradient-button px-4 py-3 text-sm font-semibold text-white shadow-app-soft transition hover:opacity-90 hover:shadow-lg">Tampilkan</button>
            </form>
            <a id="export-rekapitulasi" href="{{ route('portal.rekapitulasi.export-pdf', ['semester' => $selectedSemesterId]) }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center rounded-app-pill border border-border-subtle bg-slate-50 px-4 py-3 text-sm font-semibold text-text-body transition hover:bg-slate-100">Cetak/Export</a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const exportLink = document.getElementById('export-rekapitulasi');
            if (! exportLink) {
                return;
            }

            exportLink.addEventListener('click', function (event) {
                const now = new Date();
                const pad = (value) => String(value).padStart(2, '0');
                const localDatetime = now.getFullYear() + '-' + pad(now.getMonth() + 1) + '-' + pad(now.getDate()) + ' ' + pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds());
                const url = new URL(this.href, window.location.origin);
                url.searchParams.set('client_time', localDatetime);
                this.href = url.toString();
            });
        });
    </script>

    @if ($mataKuliahs->isEmpty())
        <div class="mt-8 rounded-app-card border border-border-subtle bg-slate-50 p-12 text-center">
            <p class="text-slate-500 mb-4">Belum ada mata kuliah di semester ini.</p>
            <a href="{{ route('portal.mata-kuliah.create') }}" class="inline-flex items-center justify-center rounded-app-pill bg-gradient-button px-6 py-3 text-sm font-semibold text-white shadow-app-soft transition hover:opacity-90 hover:shadow-lg">Tambah Mata Kuliah</a>
        </div>
    @else
        <div class="mt-8 grid gap-6 lg:grid-cols-3">
            <div class="rounded-app-card border border-border-subtle bg-white p-5 shadow-app-soft">
                <p class="text-sm text-text-label">IP Semester</p>
                <p class="mt-3 text-3xl font-semibold text-text-heading">{{ $summary['ip_semester'] !== null ? number_format($summary['ip_semester'], 2) : '-' }}</p>
            </div>
            <div class="rounded-app-card border border-border-subtle bg-white p-5 shadow-app-soft">
                <p class="text-sm text-text-label">Total SKS Semester</p>
                <p class="mt-3 text-3xl font-semibold text-text-heading">{{ $summary['total_sks'] }}</p>
            </div>
            <div class="rounded-app-card border border-border-subtle bg-white p-5 shadow-app-soft">
                <p class="text-sm text-text-label">Status Mata Kuliah</p>
                <p class="mt-3 text-3xl font-semibold text-text-heading">{{ $summary['lengkap_count'] }} Lengkap</p>
                <p class="text-sm text-text-label">{{ $summary['belum_lengkap_count'] }} Belum Lengkap</p>
            </div>
        </div>

        <div class="mt-6 overflow-x-auto rounded-app-card border border-border-subtle bg-white p-4 shadow-app-soft">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-border-subtle text-left text-text-label">
                        <th class="px-4 py-3">Nama Mata Kuliah</th>
                        <th class="px-4 py-3">SKS</th>
                        <th class="px-4 py-3">Nilai Akhir</th>
                        <th class="px-4 py-3">Huruf Mutu</th>
                        <th class="px-4 py-3">Indeks</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($tableRows as $row)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-slate-900">{{ $row['nama_mk'] }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $row['sks'] }}</td>
                            <td class="px-4 py-3 text-slate-900 font-semibold">{{ $row['nilai_akhir'] !== null ? number_format($row['nilai_akhir'], 2) : '-' }}</td>
                            <td class="px-4 py-3 text-slate-900">{{ $row['huruf_mutu'] ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-900">{{ $row['indeks'] !== null ? number_format($row['indeks'], 2) : '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-app-pill px-3 py-1 text-xs font-semibold {{ $row['status'] === 'Lengkap' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">{{ $row['status'] }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<style>
    @media print {
        aside, header, button, select, .rounded-app-card > .flex > a {
            display: none !important;
        }
        body {
            background: white;
        }
        main {
            padding: 0 !important;
        }
        table {
            font-size: 12pt;
        }
    }
</style>
@endsection
