@extends('portal.layout')

@section('title', 'Buat Mata Kuliah')
@section('header', 'Buat Mata Kuliah Baru')

@section('content')
<div class="rounded-app-card border border-border-subtle bg-white p-6 shadow-app-soft max-w-md">
    <form method="POST" action="{{ route('portal.mata-kuliah.store') }}" class="space-y-6">
        @csrf

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Tahun Akademik</label>
            <select name="tahun_akademik_id" class="w-full rounded-app-pill border border-border-subtle bg-slate-50 px-4 py-3 text-slate-900 focus:border-brand-blue focus:ring-brand-blue/50 focus:outline-none" required>
                <option value="">-- Pilih Tahun Akademik --</option>
                @foreach ($tahunAkademiks as $tahun)
                    <option value="{{ $tahun->id }}" {{ old('tahun_akademik_id') == $tahun->id ? 'selected' : '' }}>{{ $tahun->nama }}</option>
                @endforeach
            </select>
            @error('tahun_akademik_id')
                <p class="text-rose-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Mata Kuliah</label>
            <input type="text" name="nama_mk" value="{{ old('nama_mk') }}" class="w-full rounded-app-pill border border-border-subtle bg-slate-50 px-4 py-3 text-slate-900 placeholder-slate-400 focus:border-brand-blue focus:ring-brand-blue/50 focus:outline-none" placeholder="Contoh: Pemrograman Web" required>
            @error('nama_mk')
                <p class="text-rose-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">SKS (1-6)</label>
            <input type="number" name="sks" value="{{ old('sks') }}" min="1" max="6" class="w-full rounded-app-pill border border-border-subtle bg-slate-50 px-4 py-3 text-slate-900 placeholder-slate-400 focus:border-brand-blue focus:ring-brand-blue/50 focus:outline-none" required>
            @error('sks')
                <p class="text-rose-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex flex-col gap-3 sm:flex-row">
            <button type="submit" class="flex-1 rounded-app-pill bg-gradient-button px-4 py-3 text-sm font-semibold text-white shadow-app-soft transition hover:opacity-90 hover:shadow-lg">Simpan</button>
            <a href="{{ route('portal.mata-kuliah.index') }}" class="flex-1 rounded-app-pill border border-border-subtle bg-slate-50 px-4 py-3 text-center text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Batal</a>
        </div>
    </form>
</div>
@endsection
