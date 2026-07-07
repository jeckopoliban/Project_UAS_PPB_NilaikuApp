<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KomponenNilai;
use App\Models\MataKuliah;
use App\Models\TahunAkademik;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MataKuliahController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $userId = (int) ($user?->id ?? 0);

        $query = MataKuliah::with('tahunAkademik')->where('mahasiswa_id', $userId);

        if ($request->filled('tahun_akademik_id')) {
            $query->where('tahun_akademik_id', $request->query('tahun_akademik_id'));
        }

        $items = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar mata kuliah berhasil dimuat',
            'data' => $items,
        ], 200);
    }

    public function show($id)
    {
        $user = request()->user();
        $userId = (int) ($user?->id ?? 0);

        try {
            $item = MataKuliah::where('mahasiswa_id', $userId)->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Mata kuliah tidak ditemukan atau bukan milik Anda',
                'errors' => ['id' => ['Resource tidak ditemukan']],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Mata kuliah berhasil dimuat',
            'data' => $item,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tahun_akademik_id' => ['required', 'integer', 'exists:tahun_akademiks,id'],
            'nama_mk' => ['required', 'string', 'max:255'],
            'sks' => ['required', 'integer', 'min:1', 'max:6'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $userId = (int) ($user?->id ?? 0);

        $tahunAkademik = TahunAkademik::where('mahasiswa_id', $userId)
            ->where('id', $request->input('tahun_akademik_id'))
            ->first();

        if (! $tahunAkademik) {
            return response()->json([
                'success' => false,
                'message' => 'Tahun akademik tidak ditemukan atau bukan milik Anda',
                'errors' => ['tahun_akademik_id' => ['Tahun akademik tidak valid']],
            ], 422);
        }

        $item = MataKuliah::create([
            'mahasiswa_id' => $userId,
            'tahun_akademik_id' => $request->input('tahun_akademik_id'),
            'nama_mk' => $request->input('nama_mk'),
            'sks' => $request->input('sks'),
        ]);

        app(AuditLogService::class)->record(
            $request,
            'api_create_mata_kuliah',
            'Menambahkan mata kuliah via API: ' . $item->nama_mk,
            $userId,
        );

        return response()->json([
            'success' => true,
            'message' => 'Mata kuliah berhasil dibuat',
            'data' => $item,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $userId = (int) ($user?->id ?? 0);

        try {
            $item = MataKuliah::where('mahasiswa_id', $userId)->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Mata kuliah tidak ditemukan atau bukan milik Anda',
                'errors' => ['id' => ['Resource tidak ditemukan']],
            ], 404);
        }

        $isNilaiUpdate = $request->has('items') || $request->has('nama_komponen_penilaian');

        $validator = Validator::make($request->all(), [
            'tahun_akademik_id' => [
                $isNilaiUpdate ? 'sometimes' : 'required',
                'integer',
                'exists:tahun_akademiks,id',
            ],
            'nama_mk' => [$isNilaiUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'sks' => [$isNilaiUpdate ? 'sometimes' : 'required', 'integer', 'min:1', 'max:6'],
            'nama_komponen_penilaian' => ['nullable', 'string', 'max:255'],
            'items' => ['sometimes', 'array', 'min:1'],
            'items.*.id' => ['nullable', 'integer', 'exists:komponen_nilais,id'],
            'items.*.nama_komponen' => ['required_with:items', 'string', 'max:255'],
            'items.*.bobot_persen' => ['required_with:items', 'numeric', 'min:0.01', 'max:100'],
            'items.*.nilai_angka' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $tahunAkademikId = (int) $request->input('tahun_akademik_id', $item->tahun_akademik_id);

        $tahunAkademik = TahunAkademik::where('mahasiswa_id', $userId)
            ->where('id', $tahunAkademikId)
            ->first();

        if (! $tahunAkademik) {
            return response()->json([
                'success' => false,
                'message' => 'Tahun akademik tidak ditemukan atau bukan milik Anda',
                'errors' => ['tahun_akademik_id' => ['Tahun akademik tidak valid']],
            ], 422);
        }

        if ($request->filled('items')) {
            $totalBobot = collect($request->input('items', []))
                ->sum(fn ($komponen) => (float) ($komponen['bobot_persen'] ?? 0));

            if ($totalBobot > 100.0001) {
                return response()->json([
                    'success' => false,
                    'message' => 'Total bobot tidak boleh lebih dari 100%.',
                    'errors' => ['items' => ['Total bobot tidak boleh lebih dari 100%.']],
                ], 422);
            }
        }

        DB::transaction(function () use ($item, $request, $tahunAkademikId, $userId) {
            $item->fill([
                'tahun_akademik_id' => $tahunAkademikId,
                'nama_mk' => $request->input('nama_mk', $item->nama_mk),
                'sks' => (int) $request->input('sks', $item->sks),
                'nama_komponen_penilaian' => $request->input(
                    'nama_komponen_penilaian',
                    $item->nama_komponen_penilaian,
                ),
            ]);
            $item->save();

            if ($request->filled('items')) {
                $items = collect($request->input('items'));
                $incomingIds = $items->pluck('id')->filter()->all();

                KomponenNilai::where('mata_kuliah_id', $item->id)
                    ->whereNotIn('id', $incomingIds)
                    ->delete();

                foreach ($items as $komponen) {
                    $data = [
                        'nama_komponen' => $komponen['nama_komponen'],
                        'bobot_persen' => $komponen['bobot_persen'],
                        'nilai_angka' => $komponen['nilai_angka'] ?? null,
                    ];

                    if (! empty($komponen['id'])) {
                        KomponenNilai::where('id', $komponen['id'])
                            ->where('mata_kuliah_id', $item->id)
                            ->update($data);
                    } else {
                        KomponenNilai::create(array_merge($data, [
                            'mahasiswa_id' => $userId,
                            'mata_kuliah_id' => $item->id,
                        ]));
                    }
                }
            }
        });

        app(AuditLogService::class)->record(
            $request,
            'api_update_mata_kuliah',
            'Memperbarui mata kuliah via API: ' . $item->nama_mk,
            $userId,
        );

        return response()->json([
            'success' => true,
            'message' => 'Mata kuliah berhasil diperbarui',
            'data' => $item->fresh(),
        ], 200);
    }

    public function destroy($id)
    {
        $user = request()->user();
        $userId = (int) ($user?->id ?? 0);

        try {
            $item = MataKuliah::where('mahasiswa_id', $userId)->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Mata kuliah tidak ditemukan atau bukan milik Anda',
                'errors' => ['id' => ['Resource tidak ditemukan']],
            ], 404);
        }
        $item->delete();

        app(AuditLogService::class)->record(
            request(),
            'api_delete_mata_kuliah',
            'Menghapus mata kuliah via API: ' . $item->nama_mk,
            $userId,
        );

        return response()->json([
            'success' => true,
            'message' => 'Mata kuliah berhasil dihapus',
            'data' => ['id' => (int) $id],
        ], 200);
    }
}
