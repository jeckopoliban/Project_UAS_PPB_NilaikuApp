@extends('portal.layout')

@section('title', 'Edit Grading Template')
@section('header', 'Edit Grading Template')

@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('portal.grading.update', $template->id) }}">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label class="block text-sm font-medium">Nama Template</label>
            <input name="nama_template" class="mt-1 rounded-app-input w-full border px-3 py-2" value="{{ old('nama_template', $template->nama_template) }}" />
            @error('nama_template') <p class="text-rose-600 text-sm">{{ $message }}</p> @enderror
        </div>

        <div id="items" class="space-y-2">
            @foreach($template->items as $idx => $it)
            <div class="flex gap-2 items-center" data-index="{{ $idx }}">
                <input type="hidden" name="items[{{ $idx }}][id]" value="{{ $it->id }}">
                <input name="items[{{ $idx }}][batas_bawah]" value="{{ $it->batas_bawah }}" class="w-24 rounded-app-input border px-2 py-1">
                <input name="items[{{ $idx }}][batas_atas]" value="{{ $it->batas_atas }}" class="w-24 rounded-app-input border px-2 py-1">
                <input name="items[{{ $idx }}][huruf_mutu]" value="{{ $it->huruf_mutu }}" class="rounded-app-input border px-2 py-1">
                <input name="items[{{ $idx }}][indeks]" value="{{ $it->indeks }}" class="w-20 rounded-app-input border px-2 py-1">
                <button type="button" class="text-rose-600 remove-item">Hapus</button>
            </div>
            @endforeach
        </div>

        <div class="mt-3">
            <button id="add-item" type="button" class="rounded-app-pill border px-3 py-1">+ Tambah Baris</button>
        </div>

        <div class="mt-6 flex gap-2">
            <button class="rounded-app-pill bg-gradient-button px-4 py-2 text-white">Simpan</button>
            <a href="{{ route('portal.grading') }}" class="rounded-app-pill border px-4 py-2">Batal</a>
        </div>
    </form>
</div>

<script>
(function(){
    const container = document.getElementById('items');
    const addBtn = document.getElementById('add-item');
    function indexCount(){
        return container.querySelectorAll('[data-index]').length;
    }
    addBtn.addEventListener('click', () => {
        const idx = indexCount();
        const div = document.createElement('div');
        div.setAttribute('data-index', idx);
        div.className = 'flex gap-2 items-center';
        div.innerHTML = `\
            <input name="items[${idx}][batas_bawah]" class="w-24 rounded-app-input border px-2 py-1">\
            <input name="items[${idx}][batas_atas]" class="w-24 rounded-app-input border px-2 py-1">\
            <input name="items[${idx}][huruf_mutu]" class="rounded-app-input border px-2 py-1">\
            <input name="items[${idx}][indeks]" class="w-20 rounded-app-input border px-2 py-1">\
            <button type="button" class="text-rose-600 remove-item">Hapus</button>`;
        container.appendChild(div);
    });
    container.addEventListener('click', (e) => {
        if (e.target.classList.contains('remove-item')) {
            const el = e.target.closest('[data-index]');
            el?.remove();
        }
    });
})();
</script>

@endsection
