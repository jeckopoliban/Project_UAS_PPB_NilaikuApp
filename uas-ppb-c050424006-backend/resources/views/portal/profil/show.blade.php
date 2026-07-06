@extends('portal.layout')

@section('title', 'Profil Saya')
@section('header', 'Profil Saya')

@section('content')
<div class="rounded-app-card border border-border-subtle bg-white p-6 shadow-app-soft max-w-3xl mx-auto">
    <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
        <div class="flex items-center gap-4">
            @if ($user->profil?->foto_profil)
                <img src="{{ asset('storage/'.$user->profil->foto_profil) }}" alt="Foto Profil" class="h-20 w-20 rounded-full object-cover border border-border-subtle" />
            @else
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-brand-blue-light text-brand-blue text-3xl font-bold">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
            @endif
            <div>
                <h3 class="text-2xl font-semibold text-text-heading">{{ $user->name }}</h3>
                <p class="text-sm text-text-label">{{ $user->email }}</p>
            </div>
        </div>
        <a href="{{ route('portal.profil.edit') }}" class="inline-flex items-center justify-center rounded-app-pill bg-gradient-button px-5 py-3 text-sm font-semibold text-white shadow-app-soft transition hover:opacity-90 hover:shadow-lg">Edit Profil</a>
    </div>

    <div class="mt-8 grid gap-6 md:grid-cols-2">
        <div class="rounded-app-card border border-border-subtle bg-slate-50 p-5">
            <p class="text-sm text-text-label">NIM / NIS</p>
            <p class="mt-2 text-lg font-semibold text-text-heading">{{ $user->profil?->nim_nis ?? '-' }}</p>
        </div>
        <div class="rounded-app-card border border-border-subtle bg-slate-50 p-5">
            <p class="text-sm text-text-label">No HP</p>
            <p class="mt-2 text-lg font-semibold text-text-heading">{{ $user->profil?->no_hp ?? '-' }}</p>
        </div>
        <div class="rounded-app-card border border-border-subtle bg-slate-50 p-5">
            <p class="text-sm text-text-label">Institusi</p>
            <p class="mt-2 text-lg font-semibold text-text-heading">{{ $user->profil?->nama_institusi ?? '-' }}</p>
        </div>
        <div class="rounded-app-card border border-border-subtle bg-slate-50 p-5">
            <p class="text-sm text-text-label">Jenis Institusi</p>
            <p class="mt-2 text-lg font-semibold text-text-heading">
                @if ($user->profil?->jenis_institusi === 'perguruan_tinggi')
                    Perguruan Tinggi
                @elseif ($user->profil?->jenis_institusi === 'sekolah')
                    Sekolah
                @else
                    -
                @endif
            </p>
        </div>
        <div class="rounded-app-card border border-border-subtle bg-slate-50 p-5">
            <p class="text-sm text-text-label">Program Studi</p>
            <p class="mt-2 text-lg font-semibold text-text-heading">{{ $user->profil?->program_studi ?? '-' }}</p>
        </div>
        <div class="rounded-app-card border border-border-subtle bg-slate-50 p-5">
            <p class="text-sm text-text-label">Target SKS</p>
            <p class="mt-2 text-lg font-semibold text-text-heading">{{ $user->profil?->target_sks ?? 144 }}</p>
        </div>
    </div>
</div>
@endsection
