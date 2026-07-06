<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AktivitasLog;
use App\Models\KomponenNilai;
use App\Models\MataKuliah;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class NilaiInputController extends Controller
{
    public function edit($mataKuliahId): View
    {
        /** @var User|null $currentUser */
        $currentUser = request()->user();
        $currentUserId = (int) ($currentUser?->id ?? 0);

        $mataKuliah = MataKuliah::where('mahasiswa_id', $currentUserId)
            ->with(['komponenNilais', 'tahunAkademik'])
            ->findOrFail($mataKuliahId);

        return view('portal.nilai-input.edit', [
            'mataKuliah' => $mataKuliah,
            'sisaBobot' => max(0, 100 - $mataKuliah->komponenNilais->sum('bobot_persen')),
        ]);
    }

    public function template(Request $request, $mataKuliahId): RedirectResponse
    {
        /** @var User|null $currentUser */
        $currentUser = $request->user();
        $currentUserId = (int) ($currentUser?->id ?? 0);

        $mataKuliah = MataKuliah::where('mahasiswa_id', $currentUserId)
            ->with('komponenNilais')
            ->findOrFail($mataKuliahId);

        $request->validate([
            'template_key' => ['required', 'integer', 'in:1,2,3'],
        ]);

        $templates = [
            1 => [
                ['nama_komponen' => 'Tugas', 'bobot_persen' => 40],
                ['nama_komponen' => 'UTS', 'bobot_persen' => 30],
                ['nama_komponen' => 'UAS', 'bobot_persen' => 30],
            ],
            2 => [
                ['nama_komponen' => 'Kuis', 'bobot_persen' => 10],
                ['nama_komponen' => 'Kehadiran', 'bobot_persen' => 10],
                ['nama_komponen' => 'UTS', 'bobot_persen' => 40],
                ['nama_komponen' => 'UAS', 'bobot_persen' => 40],
            ],
            3 => [
                ['nama_komponen' => 'Tugas', 'bobot_persen' => 30],
                ['nama_komponen' => 'UTS', 'bobot_persen' => 35],
                ['nama_komponen' => 'UAS', 'bobot_persen' => 35],
            ],
        ];

        $selected = $templates[$request->input('template_key')];
        $existingTotal = $mataKuliah->komponenNilais->sum('bobot_persen');
        $templateTotal = array_sum(array_column($selected, 'bobot_persen'));

        if ($existingTotal + $templateTotal > 100) {
            return redirect()->back()->with('error', 'Template tidak dapat diterapkan karena total bobot melebihi 100%.');
        }

        foreach ($selected as $item) {
            KomponenNilai::create([
                'mahasiswa_id' => $currentUserId,
                'mata_kuliah_id' => $mataKuliah->id,
                'nama_komponen' => $item['nama_komponen'],
                'bobot_persen' => $item['bobot_persen'],
                'nilai_angka' => null,
            ]);
        }

        return redirect()->route('portal.nilai-saya', ['tahun_akademik_id' => $mataKuliah->tahun_akademik_id])->with('success', 'Template komponen nilai berhasil diterapkan.');
    }

    public function destroy($mataKuliahId, $id): RedirectResponse
    {
        /** @var User|null $currentUser */
        $currentUser = request()->user();
        $currentUserId = (int) ($currentUser?->id ?? 0);

        $mataKuliah = MataKuliah::where('mahasiswa_id', $currentUserId)
            ->with('komponenNilais')
            ->findOrFail($mataKuliahId);

        $item = KomponenNilai::where('mahasiswa_id', $currentUserId)
            ->where('mata_kuliah_id', $mataKuliah->id)
            ->where('id', $id)
            ->firstOrFail();

        $item->delete();

        return redirect()->route('portal.nilai-saya', ['tahun_akademik_id' => $mataKuliah->tahun_akademik_id])->with('success', 'Komponen nilai berhasil dihapus.');
    }

    public function update(Request $request, $mataKuliahId): RedirectResponse
    {
        /** @var User|null $currentUser */
        $currentUser = $request->user();
        $currentUserId = (int) ($currentUser?->id ?? 0);

        $mataKuliah = MataKuliah::where('mahasiswa_id', $currentUserId)
            ->with('komponenNilais')
            ->findOrFail($mataKuliahId);

        $rules = [
            'nama_komponen_penilaian' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['nullable', 'integer', 'exists:komponen_nilais,id'],
            'items.*.nama_komponen' => ['required', 'string', 'max:255'],
            'items.*.bobot_persen' => ['required', 'numeric', 'min:0.01', 'max:100'],
            'items.*.nilai_angka' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $namaKomponenPenilaian = trim((string) $request->input('nama_komponen_penilaian', ''));

        if (blank($namaKomponenPenilaian)) {
            return redirect()->back()->with('error', 'Nama komponen penilaian harus diisi.')->withInput();
        }

        $items = collect($request->input('items', []));
        $totalBobot = $items->sum('bobot_persen');

        if ($totalBobot > 100) {
            return redirect()->back()->with('error', 'Total bobot tidak boleh lebih dari 100%.')->withInput();
        }

        DB::transaction(function () use ($namaKomponenPenilaian, $mataKuliah, $items, $currentUserId) {
            $mataKuliah->forceFill([
                'nama_komponen_penilaian' => $namaKomponenPenilaian,
            ])->save();

            $incomingIds = $items->pluck('id')->filter()->toArray();
            KomponenNilai::where('mata_kuliah_id', $mataKuliah->id)
                ->whereNotIn('id', $incomingIds)
                ->delete();

            foreach ($items as $item) {
                $data = [
                    'nama_komponen' => $item['nama_komponen'],
                    'bobot_persen' => $item['bobot_persen'],
                    'nilai_angka' => $item['nilai_angka'] ?? null,
                ];

                if (! empty($item['id'])) {
                    KomponenNilai::where('id', $item['id'])
                        ->where('mata_kuliah_id', $mataKuliah->id)
                        ->update($data);
                } else {
                    KomponenNilai::create(array_merge($data, [
                        'mahasiswa_id' => $currentUserId,
                        'mata_kuliah_id' => $mataKuliah->id,
                    ]));
                }
            }

            AktivitasLog::create([
                'mahasiswa_id' => $currentUserId,
                'aksi' => 'Memperbarui Komponen Nilai',
                'deskripsi' => 'Mata kuliah: ' . $mataKuliah->nama_mk,
            ]);
        });

        return redirect()->route('portal.nilai-saya', ['tahun_akademik_id' => $mataKuliah->tahun_akademik_id])->with('success', 'Komponen nilai berhasil disimpan.');
    }
}
