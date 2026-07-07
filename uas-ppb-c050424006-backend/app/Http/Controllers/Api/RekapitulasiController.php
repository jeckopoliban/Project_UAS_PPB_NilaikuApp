<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MataKuliah;
use App\Models\TahunAkademik;
use App\Models\User;
use App\Services\GradingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class RekapitulasiController extends Controller
{
    public function index(Request $request)
    {
        $tahunAkademikId = $request->query('tahun_akademik_id');

        if (! $tahunAkademikId) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter tahun_akademik_id wajib dikirim',
                'errors' => ['tahun_akademik_id' => ['Parameter ini wajib diisi']],
            ], 422);
        }

        $user = $request->user();
        $mahasiswaId = (int) ($user?->id ?? 0);

        $tahunAkademik = TahunAkademik::where('mahasiswa_id', $mahasiswaId)
            ->where('id', $tahunAkademikId)
            ->first();

        if (! $tahunAkademik) {
            return response()->json([
                'success' => false,
                'message' => 'Tahun akademik tidak ditemukan atau bukan milik Anda',
                'errors' => ['tahun_akademik_id' => ['Tahun akademik tidak valid']],
            ], 404);
        }

        $mataKuliahs = MataKuliah::where('mahasiswa_id', $mahasiswaId)
            ->where('tahun_akademik_id', $tahunAkademikId)
            ->with('komponenNilais')
            ->get();

        $service = app(GradingService::class);
        $mataKuliahsData = [];

        foreach ($mataKuliahs as $mk) {
            $nilaiAkhir = $service->hitungNilaiAkhir($mk->id, $mahasiswaId);
            $grading = $nilaiAkhir !== null ? $service->convert($nilaiAkhir, $tahunAkademik->grading_template_id) : null;
            $komponenTotal = (float) $mk->komponenNilais->sum('bobot_persen');
            $adaNilaiKosong = $mk->komponenNilais->contains(fn ($komponen) => $komponen->nilai_angka === null);
            $isLengkap = $mk->komponenNilais->isNotEmpty()
                && abs($komponenTotal - 100) < 0.000001
                && ! $adaNilaiKosong
                && $nilaiAkhir !== null;

            $mataKuliahsData[] = [
                'id' => $mk->id,
                'nama_mk' => $mk->nama_mk,
                'sks' => $mk->sks,
                'nama_komponen_penilaian' => $mk->nama_komponen_penilaian,
                'nilai_akhir' => $nilaiAkhir,
                'huruf_mutu' => $grading['huruf_mutu'] ?? null,
                'indeks' => $grading['indeks'] ?? null,
                'status_lengkap' => $isLengkap ? 'Lengkap' : 'Belum Lengkap',
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Rekapitulasi nilai berhasil dimuat',
            'data' => [
                'tahun_akademik_id' => $tahunAkademik->id,
                'nama_tahun_akademik' => $tahunAkademik->nama,
                'mata_kuliah' => $mataKuliahsData,
            ],
        ], 200);
    }

    public function generateSignedPdfUrl(Request $request)
    {
        $request->validate([
            'tahun_akademik_id' => ['required', 'integer'],
        ]);

        $user = $request->user();
        $userId = (int) ($user?->id ?? 0);
        $tahunAkademik = TahunAkademik::where('mahasiswa_id', $userId)
            ->where('id', $request->tahun_akademik_id)
            ->first();

        if (! $tahunAkademik) {
            return response()->json([
                'success' => false,
                'message' => 'Tahun akademik tidak ditemukan atau bukan milik Anda',
                'errors' => ['tahun_akademik_id' => ['Tahun akademik tidak valid']],
            ], 404);
        }

        $signedUrl = URL::temporarySignedRoute(
            'api.rekapitulasi.pdf.signed',
            now()->addMinutes(5),
            ['tahun_akademik_id' => $request->tahun_akademik_id, 'user' => $userId]
        );

        return response()->json([
            'success' => true,
            'data' => [
                'url' => $signedUrl,
            ],
        ]);
    }

    public function streamSignedPdf(Request $request, GradingService $gradingService)
    {
        $mahasiswaId = (int) $request->query('user');
        $selectedSemesterId = $request->query('tahun_akademik_id');

        if (! $mahasiswaId || ! $selectedSemesterId) {
            abort(404);
        }

        $user = User::findOrFail($mahasiswaId);

        $data = $this->buildPdfData((int) $selectedSemesterId, $mahasiswaId, $gradingService, $request, $user);
        $semesterName = $data['selectedSemesterName'] ?? 'Semester';
        $safeSemesterName = preg_replace('/[^A-Za-z0-9_-]+/', '-', $semesterName);
        $safeSemesterName = trim($safeSemesterName, '-');

        $pdf = Pdf::loadView('portal.rekapitulasi.pdf', $data);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('Rekapitulasi-Nilai-' . ($safeSemesterName ?: 'Semester') . '.pdf');
    }

    private function buildPdfData(int $selectedSemesterId, int $userId, GradingService $gradingService, Request $request, ?User $user = null): array
    {
        $tahunAkademiks = TahunAkademik::where('mahasiswa_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

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
            'profil' => $user?->profil,
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
