<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Artisan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemController extends Controller
{
    public function auditLogs(): View
    {
        $logs = AuditLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.system.audit-logs', ['logs' => $logs]);
    }

    public function errorLogs(): View
    {
        $logFile = storage_path('logs/laravel.log');
        $lines = [];

        if (file_exists($logFile)) {
            $content = file_get_contents($logFile);
            $lines = array_slice(explode("\n", $content), -50);
            $lines = array_reverse($lines);
        }

        return view('admin.system.error-logs', ['lines' => $lines]);
    }

    public function toggleMaintenance(): RedirectResponse
    {
        $isDown = file_exists(storage_path('framework/down'));

        if ($isDown) {
            Artisan::call('up');
            $message = 'Aplikasi kembali online';
        } else {
            Artisan::call('down');
            $message = 'Aplikasi sedang dalam maintenance';
        }

        return redirect()->back()->with('success', $message);
    }
}
