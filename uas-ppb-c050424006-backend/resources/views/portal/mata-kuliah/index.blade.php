@extends('portal.layout')

@section('title', 'Mata Kuliah')
@section('header', 'Mata Kuliah')

@section('content')
<div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div>
        <h3 class="text-xl font-semibold text-slate-900">Daftar Mata Kuliah</h3>
        <p class="text-sm text-slate-500">Lihat ringkasan mata kuliah dan kelola komponen nilai dengan cepat.</p>
    </div>
    <a href="{{ route('portal.mata-kuliah.create') }}" class="inline-flex items-center justify-center rounded-app-pill bg-gradient-button px-5 py-3 text-sm font-semibold text-white shadow-app-soft transition hover:opacity-90 hover:shadow-lg">+ Tambah</a>
</div>

<div class="mb-6 flex flex-wrap gap-3">
    <a href="{{ route('portal.mata-kuliah.index') }}" class="inline-flex rounded-app-pill px-4 py-2 text-sm font-semibold transition {{ !request('tahun_akademik_id') ? 'bg-gradient-button text-white shadow-app-soft' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">Semua</a>
    @foreach ($tahunAkademiks as $tahun)
        <a href="{{ route('portal.mata-kuliah.index', ['tahun_akademik_id' => $tahun->id]) }}" class="inline-flex rounded-app-pill px-4 py-2 text-sm font-semibold transition {{ request('tahun_akademik_id') == $tahun->id ? 'bg-gradient-button text-white shadow-app-soft' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">{{ $tahun->nama }}</a>
    @endforeach
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    @forelse ($items as $item)
        <div class="rounded-app-card border border-border-subtle bg-white p-6 shadow-app-soft">
            <div class="mb-4">
                <h4 class="text-lg font-semibold text-slate-900">{{ $item->nama_mk }}</h4>
                <p class="text-sm text-slate-500 mt-1">{{ $item->sks }} SKS</p>
                @if ($item->tahunAkademik)
                    <p class="text-xs text-slate-400 mt-2">{{ $item->tahunAkademik->nama }}</p>
                @endif
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('portal.nilai-saya') }}?tahun_akademik_id={{ $item->tahun_akademik_id }}" class="inline-flex flex-1 items-center justify-center rounded-app-pill bg-brand-blue-light/15 px-3 py-2 text-xs font-semibold text-brand-blue transition hover:opacity-90 hover:shadow-lg/25">Nilai Saya</a>
                <a href="{{ route('portal.mata-kuliah.edit', $item->id) }}" class="inline-flex items-center justify-center rounded-app-pill bg-brand-teal/15 px-3 py-2 text-xs font-semibold text-brand-teal transition hover:bg-brand-teal/25">Edit</a>
                <form method="POST" action="{{ route('portal.mata-kuliah.destroy', $item->id) }}" class="inline" onsubmit="return confirm('Yakin ingin menghapus?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center justify-center rounded-app-pill bg-rose-100 px-3 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-200">Hapus</button>
                </form>
            </div>
        </div>
    @empty
        <div class="col-span-2 rounded-app-card border border-border-subtle bg-slate-50 p-12 text-center">
            <p class="text-slate-500 mb-4">Belum ada mata kuliah</p>
            <a href="{{ route('portal.mata-kuliah.create') }}" class="inline-flex items-center justify-center rounded-app-pill bg-gradient-button px-6 py-3 text-sm font-semibold text-white shadow-app-soft transition hover:opacity-90 hover:shadow-lg">Buat Mata Kuliah Baru</a>
        </div>
    @endforelse
</div>
@endsection
