@extends('portal.layout')

@section('title', 'Tahun Akademik')
@section('header', 'Tahun Akademik')

@section('content')
<div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div>
        <h3 class="text-xl font-semibold text-slate-900">Daftar Tahun Akademik</h3>
        <p class="text-sm text-slate-500">Kelola tahun akademik untuk sinkronisasi data mata kuliah dan nilai.</p>
    </div>
    <a href="{{ route('portal.tahun-akademik.create') }}" class="inline-flex items-center justify-center rounded-app-pill bg-gradient-button px-5 py-3 text-sm font-semibold text-white shadow-app-soft transition hover:opacity-90 hover:shadow-lg">+ Tambah</a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    @forelse ($items as $item)
        <div class="rounded-app-card border border-border-subtle bg-white p-6 shadow-app-soft">
            <div class="flex justify-between items-start gap-4">
                <div>
                    <h4 class="text-lg font-semibold text-slate-900">{{ $item->nama }}</h4>
                    <span class="inline-flex rounded-app-pill bg-brand-teal/20 px-3 py-1 mt-2 text-xs font-semibold text-brand-teal {{ $item->status_aktif ? '' : 'bg-slate-100 text-slate-500' }}">{{ $item->status_aktif ? 'Aktif' : 'Nonaktif' }}</span>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('portal.tahun-akademik.edit', $item->id) }}" class="inline-flex items-center rounded-app-pill bg-brand-blue-light/15 px-3 py-2 text-xs font-semibold text-brand-blue transition hover:opacity-90 hover:shadow-lg/25">Edit</a>
                    <form method="POST" action="{{ route('portal.tahun-akademik.destroy', $item->id) }}" class="inline" onsubmit="return confirm('Yakin ingin menghapus?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center rounded-app-pill bg-rose-100 px-3 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-200">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-2 rounded-app-card border border-border-subtle bg-slate-50 p-12 text-center">
            <p class="text-slate-500 mb-4">Belum ada tahun akademik</p>
            <a href="{{ route('portal.tahun-akademik.create') }}" class="inline-flex items-center justify-center rounded-app-pill bg-gradient-button px-6 py-3 text-sm font-semibold text-white shadow-app-soft transition hover:opacity-90 hover:shadow-lg">Buat Tahun Akademik Baru</a>
        </div>
    @endforelse
</div>
@endsection
