@extends('portal.layout')

@section('title', 'IP Sementara')
@section('header', 'Indeks Prestasi Sementara')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="rounded-app-card border border-border-subtle bg-white p-6 shadow-app-soft">
        <h3 class="text-sm font-semibold text-slate-500 mb-4">IP Sementara</h3>
        @if ($ipk !== null)
            <p class="text-4xl font-bold text-slate-900">{{ number_format($ipk, 2) }}</p>
            <p class="text-sm text-slate-500 mt-2">Temporary grade index based on current entries</p>
        @else
            <p class="text-lg text-slate-500">Data tidak tersedia</p>
        @endif
    </div>
    <div class="rounded-app-card border border-border-subtle bg-white p-6 shadow-app-soft">
        <h3 class="text-sm font-semibold text-slate-500 mb-4">Target IPK</h3>
        <form method="POST" action="{{ route('portal.nilai.target-ipk.update') }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2" for="target_ipk">Target IPK</label>
                    <input id="target_ipk" name="target_ipk" type="number" step="0.01" min="0" max="4.00" value="{{ old('target_ipk', $target_ipk) }}" class="w-full rounded-app-pill border border-border-subtle bg-slate-50 px-4 py-3 text-slate-900 focus:border-brand-blue focus:ring-brand-blue/50 focus:outline-none">
                    @error('target_ipk')<p class="text-rose-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-end">
                    <button type="submit" class="inline-flex items-center justify-center rounded-app-pill bg-gradient-button px-4 py-3 text-sm font-semibold text-white shadow-app-soft transition hover:opacity-90 hover:shadow-lg">Simpan Target</button>
                </div>
            </div>
            @if ($target_ipk !== null)
                <p class="text-sm text-slate-500">Target IPK saat ini: <strong>{{ number_format($target_ipk, 2) }}</strong></p>
            @else
                <p class="text-sm text-slate-500">Belum disetel. Isi target di atas untuk mulai memantau progress.</p>
            @endif
        </form>
    </div>
    <div class="rounded-app-card border border-border-subtle bg-white p-6 shadow-app-soft">
        <h3 class="text-sm font-semibold text-slate-500 mb-4">Selisih Target</h3>
        @if ($target_ipk !== null && $ipk !== null)
            <p class="text-4xl font-bold text-slate-900">{{ number_format(max(0, $target_ipk - $ipk), 2) }}</p>
            <p class="text-sm text-slate-500 mt-2">Selisih dari target IPK</p>
        @else
            <p class="text-lg text-slate-500">Tidak tersedia</p>
        @endif
    </div>
</div>

@if ($breakdown && count($breakdown) > 0)
    <div class="space-y-6">
        @foreach ($breakdown as $semester)
            <div class="rounded-app-card border border-border-subtle bg-white p-6 shadow-app-soft">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">{{ $semester['nama_tahun'] }}</h3>
                        <p class="text-sm text-slate-500">{{ $semester['total_sks'] }} SKS</p>
                    </div>
                    @if ($semester['ip'] !== null)
                        <div class="text-right">
                            <p class="text-3xl font-bold text-slate-900">{{ number_format($semester['ip'], 2) }}</p>
                            <p class="text-sm text-slate-500">IP Semester</p>
                        </div>
                    @endif
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-border-subtle text-slate-500">
                                <th class="px-4 py-3 text-left">Mata Kuliah</th>
                                <th class="px-4 py-3 text-left">SKS</th>
                                <th class="px-4 py-3 text-left">Nilai</th>
                                <th class="px-4 py-3 text-left">Huruf</th>
                                <th class="px-4 py-3 text-left">Indeks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach ($semester['mata_kuliah'] as $mk)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-4 py-3 text-slate-900">{{ $mk['nama_mk'] }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ $mk['sks'] }}</td>
                                    <td class="px-4 py-3 text-slate-900 font-semibold">{{ number_format($mk['nilai_akhir'], 2) }}</td>
                                    <td class="px-4 py-3"><span class="inline-flex rounded-full bg-brand-blue/10 px-2 py-1 text-xs font-semibold text-brand-blue">{{ $mk['huruf_mutu'] }}</span></td>
                                    <td class="px-4 py-3 text-slate-900 font-semibold">{{ number_format($mk['indeks'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="rounded-app-card border border-border-subtle bg-white p-12 text-center shadow-app-soft">
        <p class="text-slate-500">Belum ada data yang bisa dihitung</p>
    </div>
@endif
@endsection
