<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardStatsService;
use Illuminate\Http\Request;

class DashboardStatsController extends Controller
{
    public function index(DashboardStatsService $dashboardStatsService)
    {
        $user = request()->user();
        $stats = $dashboardStatsService->getStats((int) ($user?->id ?? 0));

        return response()->json([
            'success' => true,
            'message' => 'Statistik dashboard berhasil dimuat',
            'data' => [
                'total_sks' => $stats['total_sks'],
                'total_mata_kuliah' => $stats['total_mata_kuliah'],
                'total_semester' => $stats['total_semester'],
                'semester_aktif' => $stats['semester_aktif'],
                'ip_semester_terakhir' => $stats['ip_semester_terakhir'],
                'ipk_kumulatif' => $stats['ipk_kumulatif'],
                'sks_lulus' => $stats['sks_lulus'],
                'target_ipk' => $stats['target_ipk'],
                'target_sks' => $stats['target_sks'],
                'status_target_tercapai' => $stats['status_target_tercapai'],
                'progress_sks' => $stats['progress_sks'],
                'progress_semester' => $stats['progress_semester'],
                'ip_trend' => $stats['ip_trend'],
                'reminders' => $stats['reminders'],
                'completion' => $stats['completion'],
                'mata_kuliah_terbaru' => $stats['mata_kuliah_terbaru'],
                'aktivitas_terakhir' => $stats['aktivitas_terakhir'],
            ],
        ], 200);
    }
}
