@extends('admin.layout')

@section('title', 'Grading Template')
@section('header', 'Grading Template Management')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h3 class="text-xl font-semibold text-text-heading">Daftar Template Grading</h3>
        <p class="text-sm text-text-body">Kelola skala nilai dan template grading global.</p>
    </div>
    <a href="{{ route('admin.grading-template.create') }}" class="inline-flex items-center justify-center rounded-app-pill bg-gradient-button px-4 py-2 text-sm font-semibold text-white shadow-app-soft transition hover:opacity-95">+ Tambah Template</a>
</div>

<div class="space-y-4">
    @forelse ($items as $template)
        <div class="bg-white rounded-app-card border border-border-subtle p-6 shadow-app-card">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="flex-1">
                    <h4 class="text-lg font-semibold text-text-heading">{{ $template->nama_template }}</h4>
                    @if ($template->is_default)
                        <span class="inline-flex rounded-app-pill bg-brand-blue-light/15 px-2 py-1 text-xs font-semibold text-brand-blue">DEFAULT</span>
                    @endif
                    <p class="text-sm text-text-muted mt-2">{{ $template->items->count() }} Item</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('admin.grading-template.edit', $template->id) }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-app-pill bg-slate-100 px-3 py-2 text-sm font-semibold text-text-heading transition hover:bg-slate-200">Edit</a>
                    <form method="POST" action="{{ route('admin.grading-template.destroy', $template->id) }}" class="inline" onsubmit="return confirm('Yakin ingin menghapus?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center justify-center whitespace-nowrap rounded-app-pill bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-600 transition hover:bg-rose-100">Hapus</button>
                    </form>
                </div>
            </div>

            @if ($template->items->count() > 0)
                <div class="mt-4 pt-4 border-t border-border-subtle">
                    <p class="text-xs text-text-muted mb-2">Mapping Nilai:</p>
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($template->items as $item)
                            <div class="bg-white rounded-app-card p-3 text-center border border-border-subtle">
                                <p class="text-xs text-text-muted">{{ $item->batas_bawah }}-{{ $item->batas_atas }}</p>
                                <p class="text-sm font-semibold text-primary-start">{{ $item->huruf_mutu }}</p>
                                <p class="text-xs text-text-muted">({{ $item->indeks }})</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @empty
        <div class="text-center py-12 bg-white rounded-app-card border border-border-subtle shadow-app-card">
            <p class="text-text-body mb-4">Belum ada template grading</p>
            <a href="{{ route('admin.grading-template.create') }}" class="inline-flex items-center justify-center rounded-app-pill bg-gradient-button px-6 py-2 text-sm font-semibold text-white shadow-app-soft transition hover:opacity-95">Buat Template Pertama</a>
        </div>
    @endforelse
</div>
@endsection
