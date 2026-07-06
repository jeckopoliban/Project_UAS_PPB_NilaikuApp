@extends('portal.layout')

@section('title', 'Grading Templates')
@section('header', 'Grading Templates')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold">Template Sistem</h3>
        <a href="{{ route('portal.grading.create') }}" class="inline-flex items-center justify-center rounded-app-pill bg-gradient-button px-4 py-2 text-sm font-semibold text-white">+ Buat Skala Baru</a>
    </div>

    <div class="grid grid-cols-1 gap-4">
        @foreach($templates->where('mahasiswa_id', null) as $tpl)
            @php $isActive = $tpl->id === $activeTemplateId; @endphp
            <div class="p-4 rounded-app-card bg-white border border-border-subtle">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h4 class="font-medium">{{ $tpl->nama_template }}</h4>
                        <p class="text-sm text-text-label">Template sistem</p>
                    </div>
                    @if($isActive)
                        <span class="rounded-full bg-emerald-100 text-emerald-700 px-3 py-1 text-xs font-semibold">Aktif</span>
                    @else
                        <form method="POST" action="{{ route('portal.grading.set-active', $tpl->id) }}">
                            @csrf
                            <button type="submit" class="rounded-full border border-brand-blue text-brand-blue px-3 py-1 text-xs font-semibold">Gunakan Template Ini</button>
                        </form>
                    @endif
                </div>
                <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
                    @foreach($tpl->items as $it)
                        <div class="p-2 border rounded">{{ $it->batas_bawah }} - {{ $it->batas_atas }}: {{ $it->huruf_mutu }} ({{ $it->indeks }})</div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <h3 class="text-lg font-semibold">Template Saya</h3>
    <div class="grid grid-cols-1 gap-4">
        @foreach($templates->where('mahasiswa_id', auth()->id()) as $tpl)
            @php $isActive = $tpl->id === $activeTemplateId; @endphp
            <div class="p-4 rounded-app-card bg-white border border-border-subtle">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h4 class="font-medium">{{ $tpl->nama_template }}</h4>
                        <p class="text-sm text-text-label">Template privat saya</p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($isActive)
                            <span class="rounded-full bg-emerald-100 text-emerald-700 px-3 py-1 text-xs font-semibold">Aktif</span>
                        @else
                            <form method="POST" action="{{ route('portal.grading.set-active', $tpl->id) }}">
                                @csrf
                                <button type="submit" class="rounded-full border border-brand-blue text-brand-blue px-3 py-1 text-xs font-semibold">Gunakan Template Ini</button>
                            </form>
                        @endif
                        <a href="{{ route('portal.grading.edit', $tpl->id) }}" class="rounded-full border px-3 py-1 text-sm">Edit</a>
                        <form method="POST" action="{{ route('portal.grading.destroy', $tpl->id) }}" class="delete-template-form">
                            @csrf
                            @method('DELETE')
                            <button type="button" data-id="{{ $tpl->id }}" class="text-rose-600 open-delete-modal">Hapus</button>
                        </form>
                    </div>
                </div>
                <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
                    @foreach($tpl->items as $it)
                        <div class="p-2 border rounded">{{ $it->batas_bawah }} - {{ $it->batas_atas }}: {{ $it->huruf_mutu }} ({{ $it->indeks }})</div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Modal -->
<div id="confirm-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-40">
    <div class="bg-white rounded p-6 max-w-md w-full">
        <h3 class="font-semibold text-lg">Konfirmasi</h3>
        <p class="mt-2">Apakah Anda yakin ingin menghapus template ini? Tindakan ini tidak dapat dibatalkan.</p>
        <div class="mt-4 flex justify-end gap-2">
            <button id="modal-cancel" class="rounded-app-pill border px-4 py-2">Batal</button>
            <button id="modal-confirm" class="rounded-app-pill bg-rose-600 px-4 py-2 text-white">Hapus</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    const modal = document.getElementById('confirm-modal');
    const modalConfirm = document.getElementById('modal-confirm');
    const modalCancel = document.getElementById('modal-cancel');
    let targetForm = null;

    document.querySelectorAll('.open-delete-modal').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const form = e.target.closest('form');
            targetForm = form;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });
    });

    modalCancel.addEventListener('click', () => {
        modal.classList.remove('flex');
        modal.classList.add('hidden');
        targetForm = null;
    });

    modalConfirm.addEventListener('click', () => {
        if (targetForm) targetForm.submit();
    });
});
</script>
@endsection
