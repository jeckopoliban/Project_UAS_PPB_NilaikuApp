@extends('portal.layout')

@section('title', 'Edit Komponen Nilai')
@section('header', 'Edit Komponen Nilai')

@section('content')
<div class="rounded-app-card border border-border-subtle bg-white p-6 shadow-app-soft max-w-md">
    @if ($mataKuliah)
        <div class="space-y-3 rounded-app-card border border-border-subtle bg-slate-50 p-4">
            <p class="text-sm text-slate-500">Mata Kuliah: <span class="font-semibold text-slate-900">{{ $mataKuliah->nama_mk }}</span></p>
            <p class="text-sm text-slate-500">Sisa Kuota Bobot (selain item ini): <span class="font-semibold text-slate-900">{{ $sisaBobot }}%</span></p>
        </div>

        <form method="POST" action="{{ route('portal.nilai-input.update', $mataKuliah->id) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Komponen Penilaian</label>
                <input type="text" name="nama_komponen_penilaian" value="{{ old('nama_komponen_penilaian', $mataKuliah->nama_komponen_penilaian) }}" class="w-full rounded-app-pill border border-border-subtle bg-slate-50 px-4 py-3 text-slate-900 placeholder-slate-400 focus:border-brand-blue focus:ring-brand-blue/50 focus:outline-none" required>
                @error('nama_komponen_penilaian')
                    <p class="text-rose-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Nilai Angka (Opsional)</label>
                <input type="number" name="nilai_angka" value="{{ old('nilai_angka', $item->nilai_angka) }}" step="0.01" min="0" max="100" class="w-full rounded-app-pill border border-border-subtle bg-slate-50 px-4 py-3 text-slate-900 placeholder-slate-400 focus:border-brand-blue focus:ring-brand-blue/50 focus:outline-none" placeholder="0-100">
                @error('nilai_angka')
                    <p class="text-rose-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Bobot Persen</label>
                <input type="number" name="bobot_persen" value="{{ old('bobot_persen', $item->bobot_persen) }}" step="0.01" min="0.01" max="{{ $sisaBobot + $item->bobot_persen }}" class="w-full rounded-app-pill border border-border-subtle bg-slate-50 px-4 py-3 text-slate-900 placeholder-slate-400 focus:border-brand-blue focus:ring-brand-blue/50 focus:outline-none" required>
                @error('bobot_persen')
                    <p class="text-rose-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <button type="submit" class="flex-1 rounded-app-pill bg-gradient-button px-4 py-3 text-sm font-semibold text-white shadow-app-soft transition hover:opacity-90 hover:shadow-lg">Simpan Perubahan</button>
                <a href="{{ route('portal.nilai-saya', ['tahun_akademik_id' => $mataKuliah->tahun_akademik_id]) }}" class="flex-1 rounded-app-pill border border-border-subtle bg-slate-50 px-4 py-3 text-center text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Batal</a>
            </div>
        </form>
    @else
        <p class="text-rose-500">Mata kuliah tidak ditemukan</p>
    @endif
</div>
@endsection
