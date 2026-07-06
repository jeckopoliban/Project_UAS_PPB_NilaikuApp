@extends('admin.layout')

@section('title', 'Edit Template Grading')
@section('header', 'Edit Template Grading')

@section('content')
<div class="max-w-4xl">
    <form method="POST" action="{{ route('admin.grading-template.update', $template->id) }}" class="space-y-6">
        @csrf
        @method('PATCH')

        <div class="bg-white rounded-app-card border border-border-subtle p-6 shadow-app-card">
            <div class="grid gap-4">
                <div>
                    <label class="block text-sm font-medium text-text-label mb-2">Nama Template</label>
                    <input type="text" name="nama_template" value="{{ old('nama_template', $template->nama_template) }}" class="w-full px-4 py-3 bg-slate-50 border border-border-subtle rounded-app-input text-text-heading placeholder:text-text-muted focus:outline-none focus:border-primary-start" required>
                    @error('nama_template')
                        <p class="text-rose-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-3">
                    <input type="checkbox" id="is_default" name="is_default" value="1" {{ old('is_default', $template->is_default) ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-brand-blue focus:ring-brand-blue/50">
                    <label for="is_default" class="text-sm text-text-body">Template Default</label>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-app-card border border-border-subtle p-6 shadow-app-card">
            <div class="flex items-center justify-between gap-4 mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-text-heading">Item Grading</h3>
                    <p class="text-sm text-text-body">Ubah rentang nilai, huruf mutu, dan indeks untuk template ini.</p>
                </div>
                <button id="add-item" type="button" class="rounded-app-pill border px-3 py-2 text-sm font-semibold">+ Tambah Baris</button>
            </div>

            <div class="overflow-x-auto">
                <div class="flex gap-2 items-center font-semibold text-sm px-1 mb-2">
                    <div class="w-24">Batas Bawah</div>
                    <div class="w-24">Batas Atas</div>
                    <div class="flex-1">Huruf Mutu</div>
                    <div class="w-20">Indeks</div>
                    <div class="w-16"></div>
                </div>

                <div id="items" class="space-y-3">
                    @php $oldItems = old('items', $template->items->toArray()); @endphp
                    @foreach($oldItems as $idx => $item)
                        <div class="flex gap-2 items-center" data-index="{{ $idx }}">
                            @if(!empty($item['id']))
                                <input type="hidden" name="items[{{ $idx }}][id]" value="{{ $item['id'] }}">
                            @endif
                            <input type="number" name="items[{{ $idx }}][batas_bawah]" value="{{ $item['batas_bawah'] ?? '' }}" placeholder="80" class="w-24 rounded-app-input border border-border-subtle bg-slate-50 px-3 py-2" step="0.01" required>
                            <input type="number" name="items[{{ $idx }}][batas_atas]" value="{{ $item['batas_atas'] ?? '' }}" placeholder="100" class="w-24 rounded-app-input border border-border-subtle bg-slate-50 px-3 py-2" step="0.01" required>
                            <input type="text" name="items[{{ $idx }}][huruf_mutu]" value="{{ $item['huruf_mutu'] ?? '' }}" placeholder="A" class="flex-1 rounded-app-input border border-border-subtle bg-slate-50 px-3 py-2" required>
                            <input type="number" name="items[{{ $idx }}][indeks]" value="{{ $item['indeks'] ?? '' }}" placeholder="4" class="w-20 rounded-app-input border border-border-subtle bg-slate-50 px-3 py-2" step="0.01" min="0" max="4" required>
                            <button type="button" class="text-rose-600 remove-item">Hapus</button>
                        </div>
                    @endforeach
                </div>
            </div>

            @if ($errors->any())
                <div class="rounded-lg border border-rose-200 bg-rose-50 p-3 mt-4 text-sm text-rose-700">
                    <ul class="list-disc ps-5">
                        @foreach ($errors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
            <button type="submit" class="flex-1 inline-flex items-center justify-center rounded-app-pill bg-gradient-button px-6 py-3 text-sm font-semibold text-white shadow-app-soft transition hover:opacity-95">Simpan Perubahan</button>
            <a href="{{ route('admin.grading-template.index') }}" class="flex-1 inline-flex items-center justify-center rounded-app-pill bg-slate-100 px-6 py-3 text-sm font-semibold text-text-heading transition hover:bg-slate-200">Batal</a>
        </div>
    </form>
</div>

<script>
(function(){
    const container = document.getElementById('items');
    const addBtn = document.getElementById('add-item');

    function getNextIndex() {
        return container.querySelectorAll('[data-index]').length;
    }

    addBtn.addEventListener('click', () => {
        const idx = getNextIndex();
        const div = document.createElement('div');
        div.setAttribute('data-index', idx);
        div.className = 'flex gap-2 items-center';
        div.innerHTML = `
            <input type="number" name="items[${idx}][batas_bawah]" class="w-24 rounded-app-input border border-border-subtle bg-slate-50 px-3 py-2" step="0.01" required>
            <input type="number" name="items[${idx}][batas_atas]" class="w-24 rounded-app-input border border-border-subtle bg-slate-50 px-3 py-2" step="0.01" required>
            <input type="text" name="items[${idx}][huruf_mutu]" class="flex-1 rounded-app-input border border-border-subtle bg-slate-50 px-3 py-2" required>
            <input type="number" name="items[${idx}][indeks]" class="w-20 rounded-app-input border border-border-subtle bg-slate-50 px-3 py-2" step="0.01" min="0" max="4" required>
            <button type="button" class="text-rose-600 remove-item">Hapus</button>`;
        container.appendChild(div);
    });

    container.addEventListener('click', (e) => {
        if (e.target.classList.contains('remove-item')) {
            const row = e.target.closest('[data-index]');
            row?.remove();
        }
    });
})();
</script>
@endsection
