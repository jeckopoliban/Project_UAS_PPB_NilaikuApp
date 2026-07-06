<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MataKuliah;
use App\Services\GradingService;
use Illuminate\Http\Request;

class NilaiController extends Controller
{
    public function nilaiAkhir($mataKuliahId)
    {
        $user = request()->user();
        $mahasiswaId = (int) ($user?->id ?? 0);

        $mataKuliah = MataKuliah::where('mahasiswa_id', $mahasiswaId)
            ->where('id', $mataKuliahId)
            ->first();

        if (! $mataKuliah) {
            return response()->json([
                'success' => false,
                'message' => 'Mata kuliah tidak ditemukan atau bukan milik Anda',
                'errors' => ['mata_kuliah_id' => ['Mata kuliah tidak valid']],
            ], 404);
        }

        $service = app(GradingService::class);

        try {
            $nilaiAkhir = $service->hitungNilaiAkhir((int) $mataKuliahId, $mahasiswaId);

            if ($nilaiAkhir === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nilai akhir belum bisa dihitung karena total bobot belum mencapai 100%',
                    'data' => null,
                ], 422);
            }

            $grading = $service->convert($nilaiAkhir, $mataKuliah->tahunAkademik?->grading_template_id);

            return response()->json([
                'success' => true,
                'message' => 'Nilai akhir berhasil dihitung',
                'data' => [
                    'mata_kuliah_id' => $mataKuliah->id,
                    'nama_mk' => $mataKuliah->nama_mk,
                    'nilai_akhir' => $nilaiAkhir,
                    'huruf_mutu' => $grading['huruf_mutu'],
                    'indeks' => $grading['indeks'],
                ],
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => ['grading' => [$e->getMessage()]],
            ], 422);
        }
    }

    public function ipsIpk(Request $request)
    {
        $user = $request->user();
        $mahasiswaId = (int) ($user?->id ?? 0);
        $service = app(GradingService::class);
        $query = MataKuliah::where('mahasiswa_id', $mahasiswaId)
            ->with('tahunAkademik')
            ->orderBy('created_at', 'asc');

        if ($request->filled('tahun_akademik_id')) {
            $query->where('tahun_akademik_id', $request->query('tahun_akademik_id'));
        }

        $mataKuliahs = $query->get();
        $breakdown = [];
        $totalBobotIndeks = 0.0;
        $totalSks = 0;
        $semesterIds = [];

        foreach ($mataKuliahs as $mataKuliah) {
            $nilaiAkhir = $service->hitungNilaiAkhir($mataKuliah->id, $mahasiswaId);

            if ($nilaiAkhir === null) {
                continue;
            }

            $grading = $service->convert($nilaiAkhir, $mataKuliah->tahunAkademik?->grading_template_id);
            $indeks = (float) $grading['indeks'];
            $sks = (int) $mataKuliah->sks;
            $bobotIndeks = $indeks * $sks;

            $semesterId = (int) $mataKuliah->tahun_akademik_id;
            $semesterName = $mataKuliah->tahunAkademik?->nama ?? 'Tanpa tahun akademik';

            if (! isset($breakdown[$semesterId])) {
                $breakdown[$semesterId] = [
                    'tahun_akademik_id' => $semesterId,
                    'nama_tahun' => $semesterName,
                    'mata_kuliah' => [],
                    'total_sks' => 0,
                    'total_bobot_indeks' => 0.0,
                    'ip' => null,
                ];
            }

            $breakdown[$semesterId]['mata_kuliah'][] = [
                'mata_kuliah_id' => $mataKuliah->id,
                'nama_mk' => $mataKuliah->nama_mk,
                'sks' => $sks,
                'nilai_akhir' => $nilaiAkhir,
                'huruf_mutu' => $grading['huruf_mutu'],
                'indeks' => $indeks,
                'bobot_indeks' => round($bobotIndeks, 2),
            ];

            $breakdown[$semesterId]['total_sks'] += $sks;
            $breakdown[$semesterId]['total_bobot_indeks'] += $bobotIndeks;

            $totalSks += $sks;
            $totalBobotIndeks += $bobotIndeks;
        }

        foreach ($breakdown as &$semester) {
            if ($semester['total_sks'] > 0) {
                $semester['ip'] = round($semester['total_bobot_indeks'] / $semester['total_sks'], 2);
            }
            $semester['total_bobot_indeks'] = round($semester['total_bobot_indeks'], 2);
            $semester['mata_kuliah'] = array_values($semester['mata_kuliah']);
        }
        unset($semester);

        $ipk = $totalSks > 0 ? round($totalBobotIndeks / $totalSks, 2) : null;

        return response()->json([
            'success' => true,
            'message' => 'IP/IPK berhasil dihitung',
            'data' => [
                'tahun_akademik_id' => $request->filled('tahun_akademik_id') ? (int) $request->query('tahun_akademik_id') : null,
                'ip' => $request->filled('tahun_akademik_id') ? ($breakdown ? reset($breakdown)['ip'] : null) : null,
                'ipk' => $ipk,
                'breakdown' => array_values($breakdown),
            ],
        ], 200);
    }
}
