<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TahunAkademik;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TahunAkademikController extends Controller
{
    public function index(Request $request)
    {
        $userId = (int) ($request->user()?->id ?? 0);

        $items = TahunAkademik::where('mahasiswa_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar tahun akademik berhasil dimuat',
            'data' => $items,
        ], 200);
    }

    public function show(Request $request, $id)
    {
        try {
            $item = TahunAkademik::where('mahasiswa_id', (int) ($request->user()?->id ?? 0))->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tahun akademik tidak ditemukan atau bukan milik Anda',
                'errors' => ['id' => ['Resource tidak ditemukan']],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tahun akademik berhasil dimuat',
            'data' => $item,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => ['required', 'string', 'max:255'],
            'status_aktif' => ['sometimes', 'nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $payload = $validator->validated();
        $payload['mahasiswa_id'] = (int) ($request->user()?->id ?? 0);
        $payload['status_aktif'] = $payload['status_aktif'] ?? false;

        $item = TahunAkademik::create($payload);

        app(AuditLogService::class)->record(
            $request,
            'api_create_tahun_akademik',
            'Menambahkan tahun akademik via API: ' . $item->nama,
            (int) ($request->user()?->id ?? 0),
        );

        return response()->json([
            'success' => true,
            'message' => 'Tahun akademik berhasil dibuat',
            'data' => $item,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        try {
            $item = TahunAkademik::where('mahasiswa_id', (int) ($request->user()?->id ?? 0))->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tahun akademik tidak ditemukan atau bukan milik Anda',
                'errors' => ['id' => ['Resource tidak ditemukan']],
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama' => ['required', 'string', 'max:255'],
            'status_aktif' => ['sometimes', 'nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $item->fill($validator->validated());
        $item->save();

        app(AuditLogService::class)->record(
            $request,
            'api_update_tahun_akademik',
            'Memperbarui tahun akademik via API: ' . $item->nama,
            (int) ($request->user()?->id ?? 0),
        );

        return response()->json([
            'success' => true,
            'message' => 'Tahun akademik berhasil diperbarui',
            'data' => $item->fresh(),
        ], 200);
    }

    public function destroy(Request $request, $id)
    {
        try {
            $item = TahunAkademik::where('mahasiswa_id', (int) ($request->user()?->id ?? 0))->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tahun akademik tidak ditemukan atau bukan milik Anda',
                'errors' => ['id' => ['Resource tidak ditemukan']],
            ], 404);
        }
        $item->delete();

        app(AuditLogService::class)->record(
            $request,
            'api_delete_tahun_akademik',
            'Menghapus tahun akademik via API: ' . $item->nama,
            (int) ($request->user()?->id ?? 0),
        );

        return response()->json([
            'success' => true,
            'message' => 'Tahun akademik berhasil dihapus',
            'data' => ['id' => (int) $id],
        ], 200);
    }
}
