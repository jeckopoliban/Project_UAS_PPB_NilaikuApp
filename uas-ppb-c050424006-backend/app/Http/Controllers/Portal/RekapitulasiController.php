<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\MataKuliah;
use App\Models\TahunAkademik;
use App\Services\GradingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RekapitulasiController extends Controller
{
    public function index(Request $request, GradingService $gradingService): View
    {
        $currentUser = $request->user();
        $mahasiswaId = (int) ($currentUser?->id ?? 0);
        $data = $this->buildRekapitulasiData($request, $gradingService, $mahasiswaId);

        return view('portal.rekapitulasi.index', $data);
    }

    public function exportPdf(Request $request, GradingService $gradingService)
    {
        $currentUser = $request->user();
        $mahasiswaId = (int) ($currentUser?->id ?? 0);
        $data = $this->buildRekapitulasiData($request, $gradingService, $mahasiswaId);
        $semesterName = $data['selectedSemesterName'] ?? 'Semester';
        $safeSemesterName = preg_replace('/[^A-Za-z0-9_-]+/', '-', $semesterName);
        $safeSemesterName = trim($safeSemesterName, '-');

        $pdf = Pdf::loadView('portal.rekapitulasi.pdf', $data);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('Rekapitulasi-Nilai-' . ($safeSemesterName ?: 'Semester') . '.pdf');
    }

    private function buildRekapitulasiData(Request $request, GradingService $gradingService, int $userId): array
    {
        $user = $request->user();

        $tahunAkademiks = TahunAkademik::where('mahasiswa_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        $selectedSemesterId = $request->query('semester');

        if (! $selectedSemesterId) {
            $selectedSemesterId = $tahunAkademiks->firstWhere('status_aktif', true)?->id
                ?? $tahunAkademiks->first()?->id;
        }

        $selectedSemester = $tahunAkademiks->firstWhere('id', $selectedSemesterId);

        $mataKuliahs = MataKuliah::where('mahasiswa_id', $userId)
            ->where('tahun_akademik_id', $selectedSemesterId)
            ->with('tahunAkademik', 'komponenNilais')
            ->get();

        $firstSemester = TahunAkademik::where('mahasiswa_id', $userId)
            ->orderBy('created_at', 'asc')
            ->first();

        $tahunAngkatan = $this->determineTahunAngkatan($firstSemester);

        $summary = [
            'total_sks' => 0,
            'total_mata_kuliah' => $mataKuliahs->count(),
            'lengkap_count' => 0,
            'belum_lengkap_count' => 0,
            'ip_semester' => null,
            'belum_lengkap_mata_kuliah' => 0,
            'total_bobot_mutu' => 0.0,
            'tahun_angkatan' => $tahunAngkatan,
        ];

        $tableRows = [];
        $totalSksForIp = 0;
        $totalBobotIndeksForIp = 0.0;

        foreach ($mataKuliahs as $mataKuliah) {
            $nilaiAkhir = $gradingService->hitungNilaiAkhir($mataKuliah->id, $userId);
            $komponenTotal = $mataKuliah->komponenNilais->sum('bobot_persen');
            $adaNilaiKosong = $mataKuliah->komponenNilais->contains(function ($komponen) {
                return $komponen->nilai_angka === null;
            });
            $isLengkap = $mataKuliah->komponenNilais->isNotEmpty()
                && abs($komponenTotal - 100) < 0.000001
                && ! $adaNilaiKosong
                && $nilaiAkhir !== null;

            $row = [
                'kode_mk' => sprintf('MK%07d', $mataKuliah->id),
                'nama_mk' => $mataKuliah->nama_mk,
                'sks' => $mataKuliah->sks,
                'nilai_akhir' => null,
                'huruf_mutu' => null,
                'indeks' => null,
                'bobot_mutu' => null,
                'status' => $isLengkap ? 'Lengkap' : 'Belum Lengkap',
            ];

            if ($isLengkap && $nilaiAkhir !== null) {
                $grading = $gradingService->convert($nilaiAkhir, $mataKuliah->tahunAkademik?->grading_template_id);
                $row['nilai_akhir'] = $nilaiAkhir;
                $row['huruf_mutu'] = $grading['huruf_mutu'];
                $row['indeks'] = $grading['indeks'];
                $row['bobot_mutu'] = round($grading['indeks'] * $mataKuliah->sks, 2);

                $totalSksForIp += $mataKuliah->sks;
                $totalBobotIndeksForIp += $grading['indeks'] * $mataKuliah->sks;
                $summary['lengkap_count']++;
                $summary['total_bobot_mutu'] += $row['bobot_mutu'];
            } else {
                $summary['belum_lengkap_count']++;
                $summary['belum_lengkap_mata_kuliah']++;
            }

            $summary['total_sks'] += $mataKuliah->sks;
            $tableRows[] = $row;
        }

        if ($totalSksForIp > 0) {
            $summary['ip_semester'] = round($totalBobotIndeksForIp / $totalSksForIp, 2);
        }

        $clientTime = $request->query('client_time');
        $tanggalCetak = Carbon::now();

        if ($clientTime) {
            try {
                $tanggalCetak = Carbon::createFromFormat('Y-m-d H:i:s', $clientTime);
            } catch (\Exception $e) {
                $tanggalCetak = Carbon::now();
            }
        }

        return [
            'user' => $user,
            'profil' => $user->profil,
            'tahunAkademiks' => $tahunAkademiks,
            'selectedSemesterId' => $selectedSemesterId,
            'selectedSemesterName' => $selectedSemester?->nama,
            'mataKuliahs' => $mataKuliahs,
            'tableRows' => $tableRows,
            'summary' => $summary,
            'namaSemester' => $selectedSemester?->nama ?? 'Semester',
            'tanggalCetak' => $tanggalCetak->locale('id')->translatedFormat('d F Y H:i:s'),
        ];
    }

    private function determineTahunAngkatan(?TahunAkademik $semester): string
    {
        if (! $semester) {
            return '-';
        }

        if (preg_match('/(19|20)\d{2}/', $semester->nama, $matches)) {
            return $matches[0];
        }

        return Carbon::parse($semester->created_at)->year;
    }
}
