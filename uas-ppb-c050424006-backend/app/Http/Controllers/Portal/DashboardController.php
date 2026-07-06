<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Services\DashboardStatsService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(DashboardStatsService $dashboardStatsService): View
    {
        $stats = $dashboardStatsService->getStats(auth()->id());

        return view('portal.dashboard', $stats);
    }
}
