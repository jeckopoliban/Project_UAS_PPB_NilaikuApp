@extends('portal.layout')

@section('title', 'Buat Grading Template')
@section('header', 'Buat Skala Grading Baru')

@section('content')
<div class="max-w-xl">
    <form method="POST" action="{{ route('portal.grading.store') }}">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium">Nama Template</label>
            <input name="nama_template" class="mt-1 rounded-app-input w-full border px-3 py-2" value="{{ old('nama_template') }}" />
            @error('nama_template') <p class="text-rose-600 text-sm">{{ $message }}</p> @enderror
        </div>
        <div class="mt-4">
            <label class="block text-sm font-medium">Skala Grading (Opsional)</label>
            <div class="mt-2 text-sm text-text-label">Isi rentang nilai, huruf mutu, dan indeks. <strong class="text-rose-600">Wajib diisi minimal 1 baris.</strong> Contoh: 80 - 100 → A → 4</div>

            <div class="mt-3 overflow-x-auto">
                <div class="flex gap-2 items-center font-semibold text-sm mt-4 px-1">
                    <div class="w-24">Batas Bawah</div>
                    <div class="w-24">Batas Atas</div>
                    <div class="flex-1">Huruf Mutu</div>
                    <div class="w-20">Indeks</div>
                    <div class="w-16"></div>
                </div>

                <div id="items" class="space-y-2 mt-2">
                    @php $oldItems = old('items', []); @endphp
                    @if(count($oldItems) > 0)
                        @foreach($oldItems as $idx => $it)
                            <div class="flex gap-2 items-center" data-index="{{ $idx }}">
                                <input name="items[{{ $idx }}][batas_bawah]" value="{{ $it['batas_bawah'] ?? '' }}" placeholder="80" class="w-24 rounded-app-input border px-2 py-1">
                                <input name="items[{{ $idx }}][batas_atas]" value="{{ $it['batas_atas'] ?? '' }}" placeholder="100" class="w-24 rounded-app-input border px-2 py-1">
                                <input name="items[{{ $idx }}][huruf_mutu]" value="{{ $it['huruf_mutu'] ?? '' }}" placeholder="A" class="flex-1 rounded-app-input border px-2 py-1">
                                <input name="items[{{ $idx }}][indeks]" value="{{ $it['indeks'] ?? '' }}" placeholder="4" class="w-20 rounded-app-input border px-2 py-1">
                                <button type="button" class="text-rose-600 remove-item">Hapus</button>
                            </div>
                        @endforeach
                    @else
                        <div class="flex gap-2 items-center" data-index="0">
                            <input name="items[0][batas_bawah]" value="" placeholder="80" class="w-24 rounded-app-input border px-2 py-1">
                            <input name="items[0][batas_atas]" value="" placeholder="100" class="w-24 rounded-app-input border px-2 py-1">
                            <input name="items[0][huruf_mutu]" value="" placeholder="A" class="flex-1 rounded-app-input border px-2 py-1">
                            <input name="items[0][indeks]" value="" placeholder="4" class="w-20 rounded-app-input border px-2 py-1">
                            <button type="button" class="text-rose-600 remove-item">Hapus</button>
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-3">
                <button id="add-item" type="button" class="rounded-app-pill border px-3 py-1">+ Tambah Baris</button>
            </div>
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
        </div>
        <div class="flex gap-2">
            <button class="rounded-app-pill bg-gradient-button px-4 py-2 text-white">Buat</button>
            <a href="{{ route('portal.grading') }}" class="rounded-app-pill border px-4 py-2">Batal</a>
        </div>
    </form>
</div>
@endsection
