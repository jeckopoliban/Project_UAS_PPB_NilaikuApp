<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GradingTemplate;
use App\Models\GradingTemplateItem;
use App\Models\TahunAkademik;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GradingTemplateController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $userId = (int) ($user?->id ?? 0);

        $templates = GradingTemplate::where(function ($query) use ($userId) {
            $query->whereNull('mahasiswa_id')
                ->orWhere('mahasiswa_id', $userId);
        })->with('items')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar template penilaian berhasil dimuat',
            'data' => $templates,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_template' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.batas_bawah' => ['required', 'numeric', 'min:0', 'max:100'],
            'items.*.batas_atas' => ['required', 'numeric', 'min:0', 'max:100'],
            'items.*.huruf_mutu' => ['required', 'string', 'max:10'],
            'items.*.indeks' => ['required', 'numeric', 'min:0', 'max:4'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $template = DB::transaction(function () use ($request) {
                $user = $request->user();
                $userId = (int) ($user?->id ?? 0);

                $template = GradingTemplate::create([
                    'nama_template' => $request->input('nama_template'),
                    'mahasiswa_id' => $userId,
                    'is_default' => false,
                ]);

                foreach ($request->input('items') as $item) {
                    GradingTemplateItem::create([
                        'grading_template_id' => $template->id,
                        'batas_bawah' => $item['batas_bawah'],
                        'batas_atas' => $item['batas_atas'],
                        'huruf_mutu' => $item['huruf_mutu'],
                        'indeks' => $item['indeks'],
                    ]);
                }

                return $template->load('items');
            });

            app(AuditLogService::class)->record(
                $request,
                'api_create_grading_template',
                'Membuat template penilaian via API: ' . $template->nama_template,
                (int) ($request->user()?->id ?? 0),
            );

            return response()->json([
                'success' => true,
                'message' => 'Template penilaian berhasil dibuat',
                'data' => $template,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat template penilaian: ' . $e->getMessage(),
                'errors' => ['template' => [$e->getMessage()]],
            ], 422);
        }
    }

    public function show($id)
    {
        try {
            $user = request()->user();
            $userId = (int) ($user?->id ?? 0);

            $template = GradingTemplate::where(function ($query) use ($id, $userId) {
                $query->where('id', $id)
                    ->where(function ($q) use ($userId) {
                        $q->whereNull('mahasiswa_id')
                            ->orWhere('mahasiswa_id', $userId);
                    });
            })->with('items')->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Template penilaian tidak ditemukan',
                'errors' => ['id' => ['Resource tidak ditemukan']],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Template penilaian berhasil dimuat',
            'data' => $template,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        try {
            $template = GradingTemplate::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Template penilaian tidak ditemukan',
                'errors' => ['id' => ['Resource tidak ditemukan']],
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_template' => ['sometimes', 'string', 'max:255'],
            'items' => ['sometimes', 'array', 'min:1'],
            'items.*.batas_bawah' => ['numeric', 'min:0', 'max:100'],
            'items.*.batas_atas' => ['numeric', 'min:0', 'max:100'],
            'items.*.huruf_mutu' => ['string', 'max:10'],
            'items.*.indeks' => ['numeric', 'min:0', 'max:4'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $template = DB::transaction(function () use ($template, $request) {
                if ($request->filled('nama_template')) {
                    $template->update(['nama_template' => $request->input('nama_template')]);
                }

                if ($request->filled('items')) {
                    $template->items()->delete();
                    foreach ($request->input('items') as $item) {
                        GradingTemplateItem::create([
                            'grading_template_id' => $template->id,
                            'batas_bawah' => $item['batas_bawah'],
                            'batas_atas' => $item['batas_atas'],
                            'huruf_mutu' => $item['huruf_mutu'],
                            'indeks' => $item['indeks'],
                        ]);
                    }
                }

                return $template->load('items');
            });

            app(AuditLogService::class)->record(
                $request,
                'api_update_grading_template',
                'Memperbarui template penilaian via API: ' . $template->nama_template,
                (int) ($request->user()?->id ?? 0),
            );

            return response()->json([
                'success' => true,
                'message' => 'Template penilaian berhasil diperbarui',
                'data' => $template,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui template penilaian: ' . $e->getMessage(),
                'errors' => ['template' => [$e->getMessage()]],
            ], 422);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $template = GradingTemplate::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Template penilaian tidak ditemukan',
                'errors' => ['id' => ['Resource tidak ditemukan']],
            ], 404);
        }

        try {
            $template->items()->delete();
            $template->delete();

            app(AuditLogService::class)->record(
                $request,
                'api_delete_grading_template',
                'Menghapus template penilaian via API: ' . $template->nama_template,
                (int) ($request->user()?->id ?? 0),
            );

            return response()->json([
                'success' => true,
                'message' => 'Template penilaian berhasil dihapus',
                'data' => null,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus template penilaian: ' . $e->getMessage(),
                'errors' => ['template' => [$e->getMessage()]],
            ], 422);
        }
    }

    public function terapkan(Request $request, $id)
    {
        $user = $request->user();
        $userId = (int) ($user?->id ?? 0);

        try {
            $template = GradingTemplate::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Template penilaian tidak ditemukan',
                'errors' => ['id' => ['Template tidak ditemukan']],
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'tahun_akademik_ids' => ['required_without:semua', 'array'],
            'tahun_akademik_ids.*' => ['integer', 'exists:tahun_akademiks,id'],
            'semua' => ['boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            if ($request->input('semua')) {
                TahunAkademik::where('mahasiswa_id', $userId)->update([
                    'grading_template_id' => $template->id,
                ]);
            } else {
                $tahunAkademikIds = $request->input('tahun_akademik_ids', []);
                TahunAkademik::where('mahasiswa_id', $userId)
                    ->whereIn('id', $tahunAkademikIds)
                    ->update(['grading_template_id' => $template->id]);
            }

            app(AuditLogService::class)->record(
                $request,
                'api_apply_grading_template',
                'Menerapkan template penilaian via API: ' . $template->nama_template,
                $userId,
            );

            return response()->json([
                'success' => true,
                'message' => 'Template penilaian berhasil diterapkan',
                'data' => null,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menerapkan template: ' . $e->getMessage(),
                'errors' => ['template' => [$e->getMessage()]],
            ], 422);
        }
    }
}
