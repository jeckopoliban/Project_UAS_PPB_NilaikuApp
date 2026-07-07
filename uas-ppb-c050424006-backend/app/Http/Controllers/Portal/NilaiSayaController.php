<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\MataKuliah;
use App\Models\TahunAkademik;
use App\Services\GradingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NilaiSayaController extends Controller
{
    public function index(Request $request, GradingService $gradingService): View
    {
        $currentUser = $request->user();
        $mahasiswaId = (int) ($currentUser?->id ?? 0);
        $query = MataKuliah::where('mahasiswa_id', $mahasiswaId)
            ->with(['komponenNilais', 'tahunAkademik'])
            ->orderBy('tahun_akademik_id', 'desc')
            ->orderBy('nama_mk');

        if ($request->filled('tahun_akademik_id')) {
            $query->where('tahun_akademik_id', $request->query('tahun_akademik_id'));
        }

        $mataKuliahs = $query->get();

        $tahunAkademiks = TahunAkademik::where('mahasiswa_id', $mahasiswaId)
            ->orderBy('created_at', 'desc')
            ->get();

        $rows = $mataKuliahs->map(function (MataKuliah $mataKuliah) use ($gradingService, $mahasiswaId) {
            $totalBobot = $mataKuliah->komponenNilais->sum('bobot_persen');
            $adaNilaiKosong = $mataKuliah->komponenNilais->contains(function ($komponen) {
                return $komponen->nilai_angka === null;
            });
            $semesterName = $mataKuliah->tahunAkademik?->nama ?? '-';
            $komponenName = $mataKuliah->nama_komponen_penilaian ?: '-';
            $nilai = '-';
            $grade = '-';
            $status = 'Belum Ada Komponen';
            $link = route('portal.nilai-input.edit', $mataKuliah->id);

            if ($mataKuliah->komponenNilais->isEmpty()) {
                $nilai = '-';
                $status = 'Belum Ada Komponen';
            } elseif ($totalBobot < 100 || $adaNilaiKosong) {
                $nilai = 'Belum Lengkap';
                $status = 'Pending';
            } else {
                $nilaiAkhir = $gradingService->hitungNilaiAkhir($mataKuliah->id, $mahasiswaId);

                if ($nilaiAkhir !== null) {
                    $grading = $gradingService->convert($nilaiAkhir, $mataKuliah->tahunAkademik?->grading_template_id);
                    $nilai = number_format($nilaiAkhir, 2, ',', '.');
                    $grade = $grading['huruf_mutu'];
                    $status = 'Selesai';
                } else {
                    $nilai = 'Belum Lengkap';
                    $status = 'Pending';
                }
            }

            return [
                'id' => $mataKuliah->id,
                'nama_mk' => $mataKuliah->nama_mk,
                'semester' => $semesterName,
                'sks' => $mataKuliah->sks,
                'komponen_penilaian' => $komponenName,
                'nilai' => $nilai,
                'grade' => $grade,
                'status' => $status,
                'link' => $link,
            ];
        });

        return view('portal.nilai-saya.index', [
            'tahunAkademiks' => $tahunAkademiks,
            'selectedTahunAkademikId' => $request->query('tahun_akademik_id'),
            'mataKuliahs' => $rows,
        ]);
    }
}
