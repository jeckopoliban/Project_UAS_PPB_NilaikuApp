<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AuditLogService
{
    public function record(Request $request, string $aksi, ?string $deskripsi = null, ?int $userId = null): void
    {
        AuditLog::create([
            'user_id' => $userId ?? $request->user()?->id,
            'aksi' => $aksi,
            'deskripsi' => $deskripsi,
            'ip_address' => $request->ip(),
            'created_at' => Carbon::now(),
        ]);
    }

    public function recordUser(User $user, string $aksi, ?string $deskripsi = null, ?Request $request = null): void
    {
        AuditLog::create([
            'user_id' => $user->id,
            'aksi' => $aksi,
            'deskripsi' => $deskripsi,
            'ip_address' => $request?->ip(),
            'created_at' => Carbon::now(),
        ]);
    }
}
