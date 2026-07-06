@extends('portal.layout')

@section('title', 'Input Nilai')
@section('header', 'Input Nilai')

@section('content')
<div class="space-y-6 py-6">
    @if(session('success'))
        <div class="rounded-app-card border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-app-card border border-border-subtle bg-white p-6 shadow-app-soft max-w-4xl mx-auto mt-20">
        <div class="mb-6 rounded-app-card border border-border-subtle bg-slate-50 p-5">
            <p class="text-sm text-slate-500">Mata Kuliah</p>
            <h2 class="mt-1 text-xl font-semibold text-slate-900">{{ $mataKuliah->nama_mk }}</h2>
            <p class="text-sm text-slate-500">Semester: {{ $mataKuliah->tahunAkademik?->nama ?? '-' }} · SKS: {{ $mataKuliah->sks }}</p>
            <p class="text-sm text-slate-500 mt-3">Sisa Kuota Bobot: <span class="font-semibold text-slate-900">{{ $sisaBobot }}%</span></p>
        </div>

        <form method="POST" action="{{ route('portal.nilai-input.update', $mataKuliah->id) }}">
            @csrf
            @method('PUT')

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="nama_komponen_penilaian" class="block text-sm font-semibold text-slate-700 mb-2">Nama Komponen Penilaian</label>
                    <input id="nama_komponen_penilaian" name="nama_komponen_penilaian" value="{{ old('nama_komponen_penilaian', $mataKuliah->nama_komponen_penilaian) }}" class="w-full rounded-app-pill border border-border-subtle bg-slate-50 px-4 py-3 text-slate-900 focus:border-brand-blue focus:ring-brand-blue/50 focus:outline-none" placeholder="Contoh: Nilai Akhir" required>
                    @error('nama_komponen_penilaian')
                        <p class="text-rose-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex items-end">
                    <button type="button" onclick="addKomponenRow()" class="inline-flex items-center justify-center rounded-app-pill bg-gradient-button px-4 py-3 text-sm font-semibold text-white shadow-app-soft transition hover:opacity-90 hover:shadow-lg">Tambah Baris Komponen</button>
                </div>
            </div>

            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Komponen</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Bobot (%)</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Nilai Angka</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="komponen-table-body" class="divide-y divide-slate-200">
                        @php
                            $oldItems = old('items', []);
                            $rows = count($oldItems) > 0 ? $oldItems : $mataKuliah->komponenNilais->toArray();
                        @endphp

                        @foreach($rows as $index => $row)
                            <tr class="komponen-row">
                                <td class="px-4 py-3">
                                    <input type="hidden" name="items[{{ $index }}][id]" value="{{ $row['id'] ?? '' }}">
                                    <input type="text" name="items[{{ $index }}][nama_komponen]" value="{{ old('items.'.$index.'.nama_komponen', $row['nama_komponen'] ?? '') }}" class="w-full rounded-app-pill border border-border-subtle bg-slate-50 px-4 py-3 text-slate-900 focus:border-brand-blue focus:ring-brand-blue/50 focus:outline-none" placeholder="Contoh: Tugas" required>
                                    @error('items.'.$index.'.nama_komponen')
                                        <p class="text-rose-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" name="items[{{ $index }}][bobot_persen]" value="{{ old('items.'.$index.'.bobot_persen', $row['bobot_persen'] ?? '') }}" step="0.01" min="0.01" max="100" class="w-full rounded-app-pill border border-border-subtle bg-slate-50 px-4 py-3 text-slate-900 focus:border-brand-blue focus:ring-brand-blue/50 focus:outline-none" required>
                                    @error('items.'.$index.'.bobot_persen')
                                        <p class="text-rose-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" name="items[{{ $index }}][nilai_angka]" value="{{ old('items.'.$index.'.nilai_angka', $row['nilai_angka'] ?? '') }}" step="0.01" min="0" max="100" class="w-full rounded-app-pill border border-border-subtle bg-slate-50 px-4 py-3 text-slate-900 focus:border-brand-blue focus:ring-brand-blue/50 focus:outline-none" placeholder="Opsional">
                                    @error('items.'.$index.'.nilai_angka')
                                        <p class="text-rose-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="px-4 py-3">
                                    <button type="button" onclick="removeKomponenRow(this)" class="rounded-app-pill bg-rose-100 px-3 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-200">Hapus</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                <button type="submit" class="flex-1 rounded-app-pill bg-gradient-button px-4 py-3 text-sm font-semibold text-white shadow-app-soft transition hover:opacity-90 hover:shadow-lg">Simpan Nilai</button>
                <a href="{{ route('portal.nilai-saya', ['tahun_akademik_id' => $mataKuliah->tahun_akademik_id]) }}" class="flex-1 rounded-app-pill border border-border-subtle bg-slate-50 px-4 py-3 text-center text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
    function addKomponenRow() {
        const tableBody = document.getElementById('komponen-table-body');
        const rowCount = tableBody.querySelectorAll('.komponen-row').length;
        const index = rowCount;

        const row = document.createElement('tr');
        row.className = 'komponen-row';
        row.innerHTML = `
            <td class="px-4 py-3">
                <input type="hidden" name="items[${index}][id]" value="">
                <input type="text" name="items[${index}][nama_komponen]" class="w-full rounded-app-pill border border-border-subtle bg-slate-50 px-4 py-3 text-slate-900 focus:border-brand-blue focus:ring-brand-blue/50 focus:outline-none" placeholder="Contoh: Tugas" required>
            </td>
            <td class="px-4 py-3">
                <input type="number" name="items[${index}][bobot_persen]" step="0.01" min="0.01" max="100" class="w-full rounded-app-pill border border-border-subtle bg-slate-50 px-4 py-3 text-slate-900 focus:border-brand-blue focus:ring-brand-blue/50 focus:outline-none" required>
            </td>
            <td class="px-4 py-3">
                <input type="number" name="items[${index}][nilai_angka]" step="0.01" min="0" max="100" class="w-full rounded-app-pill border border-border-subtle bg-slate-50 px-4 py-3 text-slate-900 focus:border-brand-blue focus:ring-brand-blue/50 focus:outline-none" placeholder="Opsional">
            </td>
            <td class="px-4 py-3">
                <button type="button" onclick="removeKomponenRow(this)" class="rounded-app-pill bg-rose-100 px-3 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-200">Hapus</button>
            </td>
        `;
        tableBody.appendChild(row);
    }

    function removeKomponenRow(button) {
        const row = button.closest('tr');
        if (row) {
            row.remove();
        }
    }
</script>

@endsection
