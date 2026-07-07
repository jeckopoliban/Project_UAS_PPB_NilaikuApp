@extends('portal.layout')

@section('title', 'Nilai Saya')
@section('header', 'Nilai Saya')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-app-card border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-app-card border border-border-subtle bg-white p-6 shadow-app-soft">
        <form method="GET" action="{{ route('portal.nilai-saya') }}" class="grid gap-4 md:grid-cols-[1fr_auto] md:items-end">
            <div>
                <label for="tahun_akademik_id" class="block text-sm font-semibold text-slate-700 mb-2">Filter Semester</label>
                <select id="tahun_akademik_id" name="tahun_akademik_id" onchange="this.form.submit()" class="w-full rounded-lg border border-border-subtle bg-white px-4 py-3 text-slate-900">
                    <option value="">Semua Semester</option>
                    @foreach($tahunAkademiks as $tahun)
                        <option value="{{ $tahun->id }}" {{ $selectedTahunAkademikId == $tahun->id ? 'selected' : '' }}>{{ $tahun->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('portal.mata-kuliah.index') }}" class="rounded-app-pill border border-border-subtle bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Kelola Mata Kuliah</a>
            </div>
        </form>
    </div>

    @if($mataKuliahs->isNotEmpty())
        <div class="rounded-app-card border border-border-subtle bg-white p-6 shadow-app-soft overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Mata Kuliah</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Semester</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">SKS</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Komponen Penilaian</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Status</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Nilai Akhir</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Grade</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($mataKuliahs as $mataKuliah)
                        @php
                            $statusText = $mataKuliah['status'] ?? '-';
                            $statusClass = match ($statusText) {
                                'Selesai' => 'bg-emerald-100 text-emerald-700',
                                'Belum Ada Komponen' => '',
                                'Pending', 'Belum Lengkap' => 'bg-amber-100 text-amber-700',
                                default => 'bg-slate-100 text-slate-700',
                            };
                        @endphp
                        <tr>
                            <td class="whitespace-nowrap px-4 py-4 font-semibold text-slate-900">{{ $mataKuliah['nama_mk'] }}</td>
                            <td class="whitespace-nowrap px-4 py-4 text-slate-600">{{ $mataKuliah['semester'] }}</td>
                            <td class="whitespace-nowrap px-4 py-4 text-slate-600">{{ $mataKuliah['sks'] }}</td>
                            <td class="px-4 py-4 text-slate-600">{{ $mataKuliah['komponen_penilaian'] }}</td>
                            <td class="px-4 py-4">
                                @if ($statusText === 'Belum Ada Komponen')
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold" style="background-color:#FFEDD5;color:#C2410C;">{{ $statusText }}</span>
                                @else
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusText }}</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 text-slate-900">{{ $mataKuliah['nilai'] }}</td>
                            <td class="whitespace-nowrap px-4 py-4 text-slate-900">{{ $mataKuliah['grade'] }}</td>
                            <td class="whitespace-nowrap px-4 py-4">
                                <a href="{{ $mataKuliah['link'] }}" class="inline-flex items-center justify-center rounded-app-pill bg-gradient-button px-3 py-2 text-xs font-semibold text-white transition hover:opacity-90 hover:shadow-lg">Input Nilai</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="rounded-app-card border border-border-subtle bg-slate-50 p-12 text-center">
            <p class="text-slate-500">Tidak ada mata kuliah untuk ditampilkan. Tambahkan mata kuliah melalui wizard atau kelola mata kuliah.</p>
        </div>
    @endif
</div>
@endsection
