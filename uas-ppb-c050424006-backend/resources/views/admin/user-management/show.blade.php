@extends('admin.layout')

@section('title', 'Data User')
@section('header', 'Data User - ' . $user->name)

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-app-card border border-border-subtle p-6 shadow-app-card md:col-span-2">
        <h3 class="text-lg font-semibold text-text-heading mb-4">Informasi User</h3>
        <div class="space-y-4">
            <div>
                <p class="text-sm text-text-label">Nama</p>
                <p class="text-text-heading">{{ $user->name }}</p>
            </div>
            <div>
                <p class="text-sm text-text-label">Email</p>
                <p class="text-text-body">{{ $user->email }}</p>
            </div>
            <div>
                <p class="text-sm text-text-label">Role</p>
                <span class="inline-flex items-center rounded-app-pill bg-slate-100 px-3 py-1 text-xs font-semibold text-text-heading">{{ $user->role }}</span>
            </div>
            <div>
                <p class="text-sm text-text-label">Status</p>
                <span class="inline-flex items-center rounded-app-pill bg-slate-100 px-3 py-1 text-xs font-semibold {{ $user->status_aktif ? 'text-emerald-700' : 'text-rose-700' }}">{{ $user->status_aktif ? 'Aktif' : 'Nonaktif' }}</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-app-card border border-border-subtle p-6 shadow-app-card">
        <h3 class="text-lg font-semibold text-text-heading mb-4">Statistik</h3>
        <div class="space-y-4">
            <div>
                <p class="text-sm text-text-label">Tahun Akademik</p>
                <p class="text-2xl font-semibold text-text-heading">{{ $tahunAkademiks->count() }}</p>
            </div>
            <div>
                <p class="text-sm text-text-label">Mata Kuliah</p>
                <p class="text-2xl font-semibold text-text-heading">{{ $mataKuliahs->count() }}</p>
            </div>
            <div>
                <p class="text-sm text-text-label">Komponen Nilai</p>
                <p class="text-2xl font-semibold text-text-heading">{{ $komponenNilais->count() }}</p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-app-card border border-border-subtle p-6 shadow-app-card">
        <h3 class="text-lg font-semibold text-text-heading mb-4">Tahun Akademik</h3>
        @if ($tahunAkademiks->count() > 0)
            <ul class="space-y-2">
                @foreach ($tahunAkademiks as $tahun)
                    <li class="rounded-app-pill bg-slate-50 px-4 py-2 text-sm text-text-body">{{ $tahun->nama }}</li>
                @endforeach
            </ul>
        @else
            <p class="text-text-label text-sm">Belum ada tahun akademik</p>
        @endif
    </div>

    <div class="bg-white rounded-app-card border border-border-subtle p-6 shadow-app-card">
        <h3 class="text-lg font-semibold text-text-heading mb-4">Mata Kuliah</h3>
        @if ($mataKuliahs->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border-subtle bg-slate-50">
                            <th class="px-4 py-3 text-left text-text-label">Nama MK</th>
                            <th class="px-4 py-3 text-left text-text-label">SKS</th>
                            <th class="px-4 py-3 text-left text-text-label">Tahun Akademik</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach ($mataKuliahs as $mk)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3 text-text-heading">{{ $mk->nama_mk }}</td>
                                <td class="px-4 py-3 text-text-body">{{ $mk->sks }}</td>
                                <td class="px-4 py-3 text-text-body">{{ $mk->tahunAkademik?->nama ?? 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-text-label text-sm">Belum ada mata kuliah</p>
        @endif
    </div>
</div>

<div class="flex gap-3">
    <a href="{{ route('admin.user-management.index') }}" class="inline-flex items-center justify-center rounded-app-pill border border-border-subtle bg-white px-4 py-2 text-sm font-semibold text-text-heading transition hover:bg-slate-50">Kembali</a>
</div>
@endsection
