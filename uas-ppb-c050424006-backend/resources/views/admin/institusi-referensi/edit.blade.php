@extends('admin.layout')

@section('title', 'Edit Institusi')
@section('header', 'Edit Institusi Referensi')

@section('content')
<div class="bg-white rounded-app-card border border-border-subtle p-6 max-w-md shadow-app-card">
    <form method="POST" action="{{ route('admin.institusi-referensi.update', $item->id) }}" class="space-y-6">
        @csrf
        @method('PATCH')

        <div>
            <label class="block text-sm font-medium text-text-label mb-2">Nama Institusi</label>
            <input type="text" name="nama_institusi" value="{{ old('nama_institusi', $item->nama_institusi) }}" class="w-full px-4 py-2 bg-slate-50 border border-border-subtle rounded-app-input text-text-heading placeholder:text-text-muted focus:outline-none focus:border-primary-start" required>
            @error('nama_institusi')
                <p class="text-rose-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-text-label mb-2">Jenis Institusi</label>
            <select name="jenis" class="w-full px-4 py-2 bg-slate-50 border border-border-subtle rounded-app-input text-text-heading focus:outline-none focus:border-primary-start" required>
                <option value="">-- Pilih Jenis --</option>
                <option value="sekolah" {{ old('jenis', $item->jenis) === 'sekolah' ? 'selected' : '' }}>Sekolah</option>
                <option value="perguruan_tinggi" {{ old('jenis', $item->jenis) === 'perguruan_tinggi' ? 'selected' : '' }}>Perguruan Tinggi</option>
                <option value="lainnya" {{ old('jenis', $item->jenis) === 'lainnya' ? 'selected' : '' }}>Lainnya</option>
            </select>
            @error('jenis')
                <p class="text-rose-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex gap-3">
            <button type="submit" class="flex-1 inline-flex items-center justify-center rounded-app-pill bg-brand-blue-light/15 px-4 py-2 text-sm font-semibold text-brand-blue transition hover:bg-brand-blue-light/20">Simpan Perubahan</button>
            <a href="{{ route('admin.institusi-referensi.index') }}" class="flex-1 inline-flex items-center justify-center rounded-app-pill bg-slate-100 px-4 py-2 text-sm font-semibold text-text-heading transition hover:bg-slate-200 text-center">Batal</a>
        </div>
    </form>
</div>
@endsection
