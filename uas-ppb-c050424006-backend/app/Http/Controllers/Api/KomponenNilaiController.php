<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KomponenNilai;
use App\Models\MataKuliah;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KomponenNilaiController extends Controller
{
    public function index(Request $request)
    {
        if (! $request->filled('mata_kuliah_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter mata_kuliah_id wajib dikirim',
                'errors' => ['mata_kuliah_id' => ['Parameter ini wajib diisi']],
            ], 422);
        }

        $user = $request->user();
        $userId = (int) ($user?->id ?? 0);

        $mataKuliah = MataKuliah::where('mahasiswa_id', $userId)
            ->where('id', $request->query('mata_kuliah_id'))
            ->first();

        if (! $mataKuliah) {
            return response()->json([
                'success' => false,
                'message' => 'Mata kuliah tidak ditemukan atau bukan milik Anda',
                'errors' => ['mata_kuliah_id' => ['Mata kuliah tidak valid']],
            ], 404);
        }

        $items = KomponenNilai::where('mahasiswa_id', $userId)
            ->where('mata_kuliah_id', $mataKuliah->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function (KomponenNilai $item) use ($mataKuliah) {
                return array_merge($item->toArray(), [
                    'nama_komponen_penilaian' => $mataKuliah->nama_komponen_penilaian,
                ]);
            });

        return response()->json([
            'success' => true,
            'message' => 'Daftar komponen nilai berhasil dimuat',
            'data' => $items,
        ], 200);
    }

    public function show($id)
    {
        $user = request()->user();
        $userId = (int) ($user?->id ?? 0);

        try {
            $item = KomponenNilai::where('mahasiswa_id', $userId)->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Komponen nilai tidak ditemukan atau bukan milik Anda',
                'errors' => ['id' => ['Resource tidak ditemukan']],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Komponen nilai berhasil dimuat',
            'data' => $item,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mata_kuliah_id' => ['required', 'integer', 'exists:mata_kuliahs,id'],
            'nama_komponen' => ['required', 'string', 'max:255'],
            'nilai_angka' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'bobot_persen' => ['required', 'numeric', 'min:0.01', 'max:100'],
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

        $mataKuliah = MataKuliah::where('mahasiswa_id', $userId)
            ->where('id', $request->input('mata_kuliah_id'))
            ->first();

        if (! $mataKuliah) {
            return response()->json([
                'success' => false,
                'message' => 'Mata kuliah tidak ditemukan atau bukan milik Anda',
                'errors' => ['mata_kuliah_id' => ['Mata kuliah tidak valid']],
            ], 422);
        }

        $existingTotal = KomponenNilai::where('mahasiswa_id', $userId)
            ->where('mata_kuliah_id', $mataKuliah->id)
            ->sum('bobot_persen');

        $newTotal = (float) $existingTotal + (float) $request->input('bobot_persen');

        if ($newTotal > 100) {
            $sisa = round(100 - (float) $existingTotal, 2);

            return response()->json([
                'success' => false,
                'message' => 'Total bobot melebihi 100%, sisa kuota bobot: '.$sisa.'%',
                'errors' => ['bobot_persen' => ['Total bobot melebihi 100%']],
            ], 422);
        }

        $item = KomponenNilai::create([
            'mahasiswa_id' => $userId,
            'mata_kuliah_id' => $mataKuliah->id,
            'nama_komponen' => $request->input('nama_komponen'),
            'nilai_angka' => $request->input('nilai_angka'),
            'bobot_persen' => $request->input('bobot_persen'),
        ]);

        app(AuditLogService::class)->record(
            $request,
            'api_create_komponen_nilai',
            'Menambahkan komponen nilai via API: ' . $item->nama_komponen,
            $userId,
        );

        return response()->json([
            'success' => true,
            'message' => 'Komponen nilai berhasil dibuat',
            'data' => $item,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $userId = (int) ($user?->id ?? 0);

        try {
            $item = KomponenNilai::where('mahasiswa_id', $userId)->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Komponen nilai tidak ditemukan atau bukan milik Anda',
                'errors' => ['id' => ['Resource tidak ditemukan']],
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'mata_kuliah_id' => ['required', 'integer', 'exists:mata_kuliahs,id'],
            'nama_komponen' => ['required', 'string', 'max:255'],
            'nilai_angka' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'bobot_persen' => ['required', 'numeric', 'min:0.01', 'max:100'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $mataKuliah = MataKuliah::where('mahasiswa_id', $userId)
            ->where('id', $request->input('mata_kuliah_id'))
            ->first();

        if (! $mataKuliah) {
            return response()->json([
                'success' => false,
                'message' => 'Mata kuliah tidak ditemukan atau bukan milik Anda',
                'errors' => ['mata_kuliah_id' => ['Mata kuliah tidak valid']],
            ], 422);
        }

        $existingTotal = KomponenNilai::where('mahasiswa_id', $userId)
            ->where('mata_kuliah_id', $mataKuliah->id)
            ->where('id', '!=', $item->id)
            ->sum('bobot_persen');

        $newTotal = (float) $existingTotal + (float) $request->input('bobot_persen');

        if ($newTotal > 100) {
            $sisa = round(100 - (float) $existingTotal, 2);

            return response()->json([
                'success' => false,
                'message' => 'Total bobot melebihi 100%, sisa kuota bobot: '.$sisa.'%',
                'errors' => ['bobot_persen' => ['Total bobot melebihi 100%']],
            ], 422);
        }

        $item->fill($validator->validated());
        $item->save();

        app(AuditLogService::class)->record(
            $request,
            'api_update_komponen_nilai',
            'Memperbarui komponen nilai via API: ' . $item->nama_komponen,
            $userId,
        );

        return response()->json([
            'success' => true,
            'message' => 'Komponen nilai berhasil diperbarui',
            'data' => $item->fresh(),
        ], 200);
    }

    public function destroy($id)
    {
        $user = request()->user();
        $userId = (int) ($user?->id ?? 0);

        try {
            $item = KomponenNilai::where('mahasiswa_id', $userId)->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Komponen nilai tidak ditemukan atau bukan milik Anda',
                'errors' => ['id' => ['Resource tidak ditemukan']],
            ], 404);
        }
        $item->delete();

        app(AuditLogService::class)->record(
            request(),
            'api_delete_komponen_nilai',
            'Menghapus komponen nilai via API: ' . $item->nama_komponen,
            $userId,
        );

        return response()->json([
            'success' => true,
            'message' => 'Komponen nilai berhasil dihapus',
            'data' => ['id' => (int) $id],
        ], 200);
    }
}
