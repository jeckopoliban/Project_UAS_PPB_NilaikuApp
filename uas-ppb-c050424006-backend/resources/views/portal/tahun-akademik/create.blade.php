@extends('portal.layout')

@section('title', 'Buat Tahun Akademik')
@section('header', 'Buat Tahun Akademik Baru')

@section('content')
<div class="rounded-app-card border border-border-subtle bg-white p-6 shadow-app-soft max-w-md">
    <form method="POST" action="{{ route('portal.tahun-akademik.store') }}" class="space-y-6">
        @csrf

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Tahun Akademik</label>
            <input type="text" name="nama" value="{{ old('nama') }}" class="w-full rounded-app-pill border border-border-subtle bg-slate-50 px-4 py-3 text-slate-900 placeholder-slate-400 focus:border-brand-blue focus:ring-brand-blue/50 focus:outline-none" placeholder="Contoh: 2024/2025 Ganjil" required>
            @error('nama')
                <p class="text-rose-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-3">
            <input type="checkbox" id="status_aktif" name="status_aktif" value="1" {{ old('status_aktif') ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-brand-blue focus:ring-brand-blue/50">
            <label for="status_aktif" class="text-sm text-slate-600">Status Aktif</label>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row">
            <button type="submit" class="flex-1 rounded-app-pill bg-gradient-button px-4 py-3 text-sm font-semibold text-white shadow-app-soft transition hover:opacity-90 hover:shadow-lg">Simpan</button>
            <a href="{{ route('portal.tahun-akademik.index') }}" class="flex-1 rounded-app-pill border border-border-subtle bg-slate-50 px-4 py-3 text-center text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Batal</a>
        </div>
    </form>
</div>
@endsection
