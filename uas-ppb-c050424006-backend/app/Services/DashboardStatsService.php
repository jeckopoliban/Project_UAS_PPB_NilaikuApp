<?php

namespace App\Services;

use App\Models\AktivitasLog;
use App\Models\KomponenNilai;
use App\Models\MataKuliah;
use App\Models\TahunAkademik;
use App\Models\User;
use App\Services\GradingService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class DashboardStatsService
{
    public const SKS_TARGET = 0;
    public const SEMESTER_TARGET = 8;

    public function getStats(int $userId): array
    {
        $user = User::with(['profil', 'tahunAkademiks', 'mataKuliahs.tahunAkademik', 'komponenNilais'])->findOrFail($userId);
        $tahunAkademiks = $user->tahunAkademiks;
        $mataKuliahs = $user->mataKuliahs;
        $komponenNilais = KomponenNilai::where('mahasiswa_id', $userId)->get();

        $totalSks = $mataKuliahs->sum('sks');
        $totalMataKuliah = $mataKuliahs->count();
        $totalSemester = $tahunAkademiks->count();
        $semesterAktif = $tahunAkademiks->firstWhere('status_aktif', true)?->nama;

        $ipTrend = $this->buildIpTrend($userId);
        $ipSemesterTerakhir = end($ipTrend)['nilai_ip'] ?? 0.0;
        $ipkKumulatif = $this->hitungIpkKumulatif($userId) ?? 0.0;
        $sksLulus = $this->hitungSksLulus($userId);
        $targetIpK = $user->profil?->target_ipk ?? 0.0;
        $statusTargetTercapai = $targetIpK > 0 ? $ipkKumulatif >= $targetIpK : false;

        $reminders = $this->buildReminders($userId);
        $completion = $this->buildCompletion($user, $mataKuliahs, $komponenNilais, $tahunAkademiks);

        $targetSks = $user->profil?->target_sks ?? self::SKS_TARGET;

        return [
            'user' => $user,
            'total_sks' => $totalSks,
            'total_mata_kuliah' => $totalMataKuliah,
            'total_semester' => $totalSemester,
            'semester_aktif' => $semesterAktif,
            'ip_semester_terakhir' => $ipSemesterTerakhir,
            'ipk_kumulatif' => $ipkKumulatif,
            'sks_lulus' => $sksLulus,
            'target_ipk' => $targetIpK,
            'target_sks' => $targetSks,
            'status_target_tercapai' => $statusTargetTercapai,
            'progress_sks' => ['current' => $totalSks, 'target' => $targetSks],
            'progress_semester' => ['current' => $totalSemester, 'target' => self::SEMESTER_TARGET],
            'ip_trend' => $ipTrend,
            'reminders' => $reminders,
            'completion' => $completion,
            'mata_kuliah_terbaru' => $this->getLatestMataKuliah($userId),
            'aktivitas_terakhir' => AktivitasLog::where('mahasiswa_id', $userId)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
        ];
    }

    protected function buildIpTrend(int $userId): array
    {
        $semesters = TahunAkademik::where('mahasiswa_id', $userId)
            ->orderBy('created_at', 'asc')
            ->get();

        $trend = [];

        foreach ($semesters as $semester) {
            $mataKuliahs = MataKuliah::where('mahasiswa_id', $userId)
                ->where('tahun_akademik_id', $semester->id)
                ->get();

            $semesterIp = $this->hitungIpSemester($mataKuliahs, $userId);
            if ($semesterIp !== null) {
                $trend[] = ['nama_semester' => $semester->nama, 'nilai_ip' => $semesterIp];
            }
        }

        return $trend;
    }

    protected function hitungIpSemester(Collection $mataKuliahs, int $userId): ?float
    {
        $totalSks = 0;
        $totalBobotIndeks = 0.0;

        foreach ($mataKuliahs as $mataKuliah) {
            $nilaiAkhir = app(GradingService::class)->hitungNilaiAkhir($mataKuliah->id, $userId);
            if ($nilaiAkhir === null) {
                continue;
            }

            try {
                $grading = app(GradingService::class)->convert($nilaiAkhir, $mataKuliah->tahunAkademik?->grading_template_id);
            } catch (\Throwable $e) {
                continue;
            }

            $totalSks += $mataKuliah->sks;
            $totalBobotIndeks += $grading['indeks'] * $mataKuliah->sks;
        }

        return $totalSks > 0 ? round($totalBobotIndeks / $totalSks, 2) : null;
    }

    protected function hitungIpkKumulatif(int $userId): ?float
    {
        $mataKuliahs = MataKuliah::where('mahasiswa_id', $userId)->get();
        $totalSks = 0;
        $totalBobotIndeks = 0.0;

        foreach ($mataKuliahs as $mataKuliah) {
            $nilaiAkhir = app(GradingService::class)->hitungNilaiAkhir($mataKuliah->id, $userId);
            if ($nilaiAkhir === null) {
                continue;
            }

            try {
                $grading = app(GradingService::class)->convert($nilaiAkhir, $mataKuliah->tahunAkademik?->grading_template_id);
            } catch (\Throwable $e) {
                continue;
            }

            $totalSks += $mataKuliah->sks;
            $totalBobotIndeks += $grading['indeks'] * $mataKuliah->sks;
        }

        return $totalSks > 0 ? round($totalBobotIndeks / $totalSks, 2) : null;
    }

    protected function hitungSksLulus(int $userId): int
    {
        $sksLulus = 0;
        $mataKuliahs = MataKuliah::where('mahasiswa_id', $userId)->get();

        foreach ($mataKuliahs as $mataKuliah) {
            $nilaiAkhir = app(GradingService::class)->hitungNilaiAkhir($mataKuliah->id, $userId);
            if ($nilaiAkhir === null) {
                continue;
            }

            try {
                $grading = app(GradingService::class)->convert($nilaiAkhir, $mataKuliah->tahunAkademik?->grading_template_id);
            } catch (\Throwable $e) {
                continue;
            }

            if ($grading['indeks'] > 0) {
                $sksLulus += $mataKuliah->sks;
            }
        }

        return $sksLulus;
    }

    protected function buildReminders(int $userId): array
    {
        $messages = [];

        $mataKuliahs = MataKuliah::where('mahasiswa_id', $userId)->with('komponenNilais', 'tahunAkademik')->get();
        $tahunAkademiks = TahunAkademik::where('mahasiswa_id', $userId)->get();

        $mataKuliahsTanpaKomponen = $mataKuliahs->filter(fn($mk) => $mk->komponenNilais->isEmpty());
        if ($mataKuliahsTanpaKomponen->isNotEmpty()) {
            $messages[] = 'Beberapa mata kuliah belum memiliki komponen nilai.';
        }

        $mataKuliahsBobotKurang = $mataKuliahs->filter(fn($mk) => $mk->komponenNilais->sum('bobot_persen') < 100);
        if ($mataKuliahsBobotKurang->isNotEmpty()) {
            $messages[] = 'Beberapa mata kuliah belum memiliki bobot komponen lengkap.';
        }

        $mataKuliahsNilaiBelumLengkap = $mataKuliahs->filter(fn($mk) => $mk->komponenNilais->contains(fn($komponen) => $komponen->nilai_angka === null));
        if ($mataKuliahsNilaiBelumLengkap->isNotEmpty()) {
            $messages[] = 'Beberapa nilai belum terisi sepenuhnya.';
        }

        if ($tahunAkademiks->isEmpty()) {
            $messages[] = 'Belum ada tahun akademik.';
        }

        if (empty($messages)) {
            return ['Seluruh data akademik sudah lengkap.'];
        }

        return $messages;
    }

    protected function buildCompletion(User $user, Collection $mataKuliahs, Collection $komponenNilais, Collection $tahunAkademiks): array
    {
        $profilFields = [
            $user->profil?->nim_nis,
            $user->profil?->nama_institusi,
            $user->profil?->jenis_institusi,
            $user->profil?->program_studi,
        ];

        $profilCount = collect($profilFields)->filter()->count();
        $profilPercent = round(($profilCount / count($profilFields)) * 100);

        $semesterPercent = $tahunAkademiks->isNotEmpty() ? 100 : 0;
        $mataKuliahPercent = $mataKuliahs->isNotEmpty() ? 100 : 0;

        $mataKuliahSksCompleted = $mataKuliahs->filter(fn($mk) => $mk->komponenNilais->sum('bobot_persen') >= 100)->count();
        $komponenPercent = $mataKuliahs->count() > 0
            ? round(($mataKuliahSksCompleted / $mataKuliahs->count()) * 100)
            : 0;

        $nilaiFilled = $komponenNilais->filter(fn($item) => $item->nilai_angka !== null)->count();
        $nilaiPercent = $komponenNilais->count() > 0
            ? round(($nilaiFilled / $komponenNilais->count()) * 100)
            : 0;

        $overall = round(($profilPercent + $semesterPercent + $mataKuliahPercent + $komponenPercent + $nilaiPercent) / 5, 2);

        return [
            'profil' => $profilPercent,
            'semester' => $semesterPercent,
            'mata_kuliah' => $mataKuliahPercent,
            'komponen_nilai' => $komponenPercent,
            'nilai' => $nilaiPercent,
            'overall' => $overall,
        ];
    }

    protected function getLatestMataKuliah(int $userId): array
    {
        return MataKuliah::where('mahasiswa_id', $userId)
            ->with('tahunAkademik')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($mk) => [
                'nama_mk' => $mk->nama_mk,
                'nama_tahun' => $mk->tahunAkademik?->nama,
            ])
            ->toArray();
    }
}
