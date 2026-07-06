<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\GradingTemplate;
use App\Models\MataKuliah;
use App\Models\Profil;
use App\Models\TahunAkademik;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalUsers = User::count();
        $activeUsers = User::where('status_aktif', true)->count();
        $mataKuliahCount = MataKuliah::count();
        $activeSemesters = TahunAkademik::where('status_aktif', true)->count();
        $auditLogCount = AuditLog::count();
        $weeklyNewUsers = User::where('created_at', '>=', now()->subDays(7))->count();
        $activeUserPercent = $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100) : 0;

        $minDate = User::min('created_at');
        $startMonth = \Carbon\Carbon::parse($minDate)->startOfMonth();
        $endMonth = \Carbon\Carbon::now()->startOfMonth();
        
        $months = [];
        $current = $startMonth->copy();
        while ($current <= $endMonth) {
            $label = $current->format('m-Y');
            $count = User::whereYear('created_at', $current->year)
                ->whereMonth('created_at', $current->month)->count();
            $months[$label] = $count;
            $current->addMonth();
        }

        $userGrowth = collect($months)->map(function ($count, $month) {
            return ['month' => $month, 'count' => $count];
        })->values();

        // Top institusi
        $topInstitusi = Profil::selectRaw('nama_institusi, COUNT(*) as count')
            ->groupBy('nama_institusi')
            ->orderBy('count', 'desc')
            ->limit(7)
            ->get();

        // Top mata kuliah
        $topMataKuliah = MataKuliah::selectRaw('nama_mk, COUNT(*) as count')
            ->groupBy('nama_mk')
            ->orderBy('count', 'desc')
            ->limit(7)
            ->get();

        return view('admin.dashboard', [
            'totalUsers' => $totalUsers,
            'activeUsers' => $activeUsers,
            'mataKuliahCount' => $mataKuliahCount,
            'activeSemesters' => $activeSemesters,
            'auditLogCount' => $auditLogCount,
            'gradingTemplateCount' => GradingTemplate::whereNull('mahasiswa_id')->count(),
            'weeklyNewUsers' => $weeklyNewUsers,
            'activeUserPercent' => $activeUserPercent,
            'userGrowth' => $userGrowth,
            'topInstitusi' => $topInstitusi,
            'topMataKuliah' => $topMataKuliah,
        ]);
    }
}
