@extends('portal.layout')

@section('title', 'Edit Profil')
@section('header', 'Edit Profil Saya')

@section('content')
<div class="rounded-app-card border border-border-subtle bg-white p-6 shadow-app-soft max-w-3xl mx-auto">
    <form method="POST" action="{{ route('portal.profil.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid gap-6 md:grid-cols-2">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2" for="name">Nama</label>
                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" class="w-full rounded-app-pill border border-border-subtle bg-slate-50 px-4 py-3 text-slate-900 focus:border-brand-blue focus:ring-brand-blue/50 focus:outline-none" required>
                @error('name')<p class="text-rose-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2" for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ $user->email }}" class="w-full rounded-app-pill border border-border-subtle bg-slate-100 px-4 py-3 text-slate-600 focus:outline-none" readonly>
                <p class="mt-2 text-xs text-slate-500">Email adalah kredensial akun dan tidak dapat diubah dari profil mahasiswa.</p>
                @error('email')<p class="text-rose-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2" for="nim_nis">NIM / NIS</label>
                <input id="nim_nis" name="nim_nis" type="text" value="{{ old('nim_nis', $user->profil?->nim_nis) }}" class="w-full rounded-app-pill border border-border-subtle bg-slate-50 px-4 py-3 text-slate-900 focus:border-brand-blue focus:ring-brand-blue/50 focus:outline-none">
                @error('nim_nis')<p class="text-rose-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2" for="no_hp">No HP</label>
                <input id="no_hp" name="no_hp" type="text" value="{{ old('no_hp', $user->profil?->no_hp) }}" class="w-full rounded-app-pill border border-border-subtle bg-slate-50 px-4 py-3 text-slate-900 focus:border-brand-blue focus:ring-brand-blue/50 focus:outline-none">
                @error('no_hp')<p class="text-rose-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2" for="nama_institusi">Nama Institusi</label>
                <input id="nama_institusi" name="nama_institusi" type="text" value="{{ old('nama_institusi', $user->profil?->nama_institusi) }}" class="w-full rounded-app-pill border border-border-subtle bg-slate-50 px-4 py-3 text-slate-900 focus:border-brand-blue focus:ring-brand-blue/50 focus:outline-none" required>
                @error('nama_institusi')<p class="text-rose-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2" for="jenis_institusi">Jenis Institusi</label>
                <select id="jenis_institusi" name="jenis_institusi" class="w-full rounded-app-pill border border-border-subtle bg-slate-50 px-4 py-3 text-slate-900 focus:border-brand-blue focus:ring-brand-blue/50 focus:outline-none" required>
                    <option value="" disabled {{ old('jenis_institusi', $user->profil?->jenis_institusi) ? '' : 'selected' }}>Pilih jenis institusi</option>
                    <option value="perguruan_tinggi" {{ old('jenis_institusi', $user->profil?->jenis_institusi) === 'perguruan_tinggi' ? 'selected' : '' }}>Perguruan Tinggi</option>
                    <option value="sekolah" {{ old('jenis_institusi', $user->profil?->jenis_institusi) === 'sekolah' ? 'selected' : '' }}>Sekolah</option>
                </select>
                @error('jenis_institusi')<p class="text-rose-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2" for="program_studi">Program Studi</label>
                <input id="program_studi" name="program_studi" type="text" value="{{ old('program_studi', $user->profil?->program_studi) }}" class="w-full rounded-app-pill border border-border-subtle bg-slate-50 px-4 py-3 text-slate-900 focus:border-brand-blue focus:ring-brand-blue/50 focus:outline-none">
                @error('program_studi')<p class="text-rose-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2" for="target_sks">Target SKS</label>
                <input id="target_sks" name="target_sks" type="number" min="0" max="200" value="{{ old('target_sks', $user->profil?->target_sks ?? 0) }}" class="w-full rounded-app-pill border border-border-subtle bg-slate-50 px-4 py-3 text-slate-900 focus:border-brand-blue focus:ring-brand-blue/50 focus:outline-none">
                @error('target_sks')<p class="text-rose-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-2" for="foto_profil">Foto Profil</label>
                <input id="foto_profil" name="foto_profil" type="file" class="w-full rounded-app-pill border border-border-subtle bg-slate-50 px-4 py-3 text-slate-900 focus:border-brand-blue focus:ring-brand-blue/50 focus:outline-none">
                <p class="mt-2 text-sm text-slate-500">Maksimum ukuran file: 2 MB. Format gambar apa pun yang valid diterima.</p>
                @if ($user->profil?->foto_profil)
                    <div class="mt-4 flex items-center gap-4">
                        <img src="{{ asset('storage/'.$user->profil->foto_profil) }}" alt="Foto profil saat ini" class="h-24 w-24 rounded-full object-cover border border-border-subtle" />
                        <p class="text-sm text-slate-500">Foto saat ini: <span class="font-semibold">{{ basename($user->profil->foto_profil) }}</span></p>
                    </div>
                @endif
                @error('foto_profil')<p class="text-rose-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row">
            <button type="submit" class="flex-1 rounded-app-pill bg-gradient-button px-5 py-3 text-sm font-semibold text-white shadow-app-soft transition hover:opacity-90 hover:shadow-lg">Simpan Perubahan</button>
            <a href="{{ route('portal.profil.show') }}" class="flex-1 rounded-app-pill border border-border-subtle bg-slate-50 px-5 py-3 text-sm font-semibold text-slate-700 text-center transition hover:bg-slate-100">Batal</a>
        </div>
    </form>
</div>
@endsection
