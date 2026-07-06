@extends('admin.layout')

@section('title', 'Detail Template Grading')
@section('header', 'Detail Template Grading')

@section('content')
<div class="bg-white rounded-app-card border border-border-subtle p-6 max-w-4xl shadow-app-card">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-6">
        <div>
            <h3 class="text-xl font-semibold text-text-heading">{{ $template->nama_template }}</h3>
            @if ($template->is_default)
                <span class="inline-flex rounded-app-pill bg-brand-blue-light/15 px-2 py-1 text-xs font-semibold text-brand-blue">DEFAULT</span>
            @endif
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.grading-template.edit', $template->id) }}" class="inline-flex items-center justify-center rounded-app-pill bg-gradient-button px-4 py-2 text-sm font-semibold text-white shadow-app-soft transition hover:opacity-95">Edit Template</a>
            <a href="{{ route('admin.grading-template.index') }}" class="inline-flex items-center justify-center rounded-app-pill bg-slate-100 px-4 py-2 text-sm font-semibold text-text-heading transition hover:bg-slate-200">Kembali</a>
        </div>
    </div>

    <div class="bg-slate-50 rounded-app-card border border-border-subtle p-4">
        <h4 class="text-sm uppercase tracking-[0.24em] text-text-muted mb-3">Item Grading</h4>
        <div class="space-y-3">
            @forelse($template->items as $item)
                <div class="grid grid-cols-6 gap-3 items-center bg-white rounded-app-card p-3 border border-border-subtle">
                    <div class="col-span-2 text-text-heading">{{ $item->batas_bawah }} - {{ $item->batas_atas }}</div>
                    <div class="col-span-1 text-primary-start">{{ $item->huruf_mutu }}</div>
                    <div class="col-span-1 text-text-muted">{{ $item->indeks }}</div>
                    <div class="col-span-2 text-right text-text-muted">Item global</div>
                </div>
            @empty
                <p class="text-text-muted">Belum ada item.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
