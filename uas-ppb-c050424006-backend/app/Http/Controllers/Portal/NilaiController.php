<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\MataKuliah;
use App\Services\GradingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NilaiController extends Controller
{
    public function nilaiAkhir($mataKuliahId, GradingService $gradingService): View
    {
        $request = request();
        $currentUser = $request->user();
        $mahasiswaId = (int) ($currentUser?->id ?? 0);

        $mataKuliah = MataKuliah::where('mahasiswa_id', $mahasiswaId)
            ->where('id', $mataKuliahId)
            ->first();

        if (! $mataKuliah) {
            abort(404);
        }

        try {
            $nilaiAkhir = $gradingService->hitungNilaiAkhir((int) $mataKuliahId, $mahasiswaId);

            if ($nilaiAkhir === null) {
                return view('portal.nilai.detail', [
                    'mataKuliah' => $mataKuliah,
                    'nilaiAkhir' => null,
                    'grading' => null,
                    'error' => 'Nilai akhir belum bisa dihitung karena total bobot belum mencapai 100%',
                ]);
            }

            $grading = $gradingService->convert($nilaiAkhir, $mataKuliah->tahunAkademik?->grading_template_id);

            return view('portal.nilai.detail', [
                'mataKuliah' => $mataKuliah,
                'nilaiAkhir' => $nilaiAkhir,
                'grading' => $grading,
                'error' => null,
            ]);
        } catch (\Throwable $e) {
            return view('portal.nilai.detail', [
                'mataKuliah' => $mataKuliah,
                'nilaiAkhir' => null,
                'grading' => null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function ipsIpk(Request $request, GradingService $gradingService): View
    {
        $currentUser = $request->user();
        $mahasiswaId = (int) ($currentUser?->id ?? 0);
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

        foreach ($mataKuliahs as $mataKuliah) {
            $nilaiAkhir = $gradingService->hitungNilaiAkhir($mataKuliah->id, $mahasiswaId);

            if ($nilaiAkhir === null) {
                continue;
            }

            try {
                $grading = $gradingService->convert($nilaiAkhir, $mataKuliah->tahunAkademik?->grading_template_id);
            } catch (\Throwable $e) {
                continue;
            }

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

        $targetIpK = $currentUser?->profil?->target_ipk;

        return view('portal.nilai.ips-ipk', [
            'ipk' => $ipk,
            'breakdown' => array_values($breakdown),
            'target_ipk' => $targetIpK,
        ]);
    }

    public function updateTargetIpK(Request $request)
    {
        $request->validate([
            'target_ipk' => ['nullable', 'numeric', 'between:0,4.00'],
        ]);

        $user = $request->user();
        $user->profil()->updateOrCreate([
            'user_id' => $user->id,
        ], [
            'target_ipk' => $request->input('target_ipk'),
        ]);

        return redirect()->route('portal.nilai.ips-ipk')->with('success', 'Target IPK berhasil disimpan.');
    }
}
