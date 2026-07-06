@extends('admin.layout')

@section('title', 'Konfirmasi Hapus Permanen')
@section('header', 'Konfirmasi Hapus Permanen')

@section('content')
<div class="bg-white rounded-app-card border border-border-subtle p-6 max-w-2xl mx-auto shadow-app-card">
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-text-heading">Hapus Permanen User</h3>
        <p class="text-text-body mt-2">Aksi ini akan menghapus akun user dan semua data akademik (Tahun Akademik, Mata Kuliah, Komponen Nilai) secara permanen dan tidak dapat dikembalikan.</p>
    </div>

    <div class="bg-slate-50 rounded-app-card border border-border-subtle p-6 mb-6">
        <p class="text-text-body">Nama: <span class="font-semibold text-text-heading">{{ $user->name }}</span></p>
        <p class="text-text-body">Email: <span class="font-semibold text-text-heading">{{ $user->email }}</span></p>
        <p class="text-text-body">Dihapus pada: <span class="font-semibold text-text-heading">{{ $user->deleted_at?->format('d M Y H:i') }}</span></p>
    </div>

    <form method="POST" action="{{ route('admin.users.force-delete', $user->id) }}">
        @csrf
        @method('DELETE')

        <div class="space-y-4">
            <p class="text-rose-600">Pastikan Anda ingin menghapus permanen user ini. Data akademik user akan ikut dihapus secara permanen.</p>
            <div class="flex flex-col md:flex-row gap-3">
                <button type="submit" class="flex-1 inline-flex items-center justify-center rounded-app-pill bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-600 transition hover:bg-rose-100">Hapus Permanen</button>
                <a href="{{ route('admin.users.trashed') }}" class="flex-1 inline-flex items-center justify-center rounded-app-pill bg-slate-100 px-4 py-2 text-sm font-semibold text-text-heading transition hover:bg-slate-200 text-center">Batal</a>
            </div>
        </div>
    </form>
</div>
@endsection

