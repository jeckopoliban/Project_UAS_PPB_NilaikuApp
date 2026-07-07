<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Profil;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function photo(Request $request)
    {
        $profil = $request->user()->load('profil')->profil;

        if (! $profil?->foto_profil || ! Storage::disk('public')->exists($profil->foto_profil)) {
            abort(404);
        }

        $path = Storage::disk('public')->path($profil->foto_profil);

        return response()->file($path, [
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'nim_nis' => ['nullable', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:255'],
            'nama_institusi' => ['sometimes', 'nullable', 'string', 'max:255'],
            'jenis_institusi' => ['sometimes', 'nullable', 'string', 'in:perguruan_tinggi,sekolah'],
            'program_studi' => ['sometimes', 'nullable', 'string', 'max:255'],
            'target_ipk' => ['nullable', 'numeric', 'between:0,4.00'],
            'target_sks' => ['nullable', 'integer', 'between:0,200'],
            'foto_profil' => ['nullable', 'image', 'max:2048'],
            'hapus_foto_profil' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $existingProfil = $user->profil;
        $validated = $validator->validated();

        $userData = [];
        if (array_key_exists('name', $validated)) {
            $userData['name'] = $validated['name'];
        }

        $profileData = array_merge([
            'nama_institusi' => $existingProfil?->nama_institusi ?? '',
            'jenis_institusi' => $existingProfil?->jenis_institusi ?? 'perguruan_tinggi',
            'program_studi' => $existingProfil?->program_studi,
            'nim_nis' => $existingProfil?->nim_nis,
            'no_hp' => $existingProfil?->no_hp,
            'target_ipk' => $existingProfil?->target_ipk,
            'target_sks' => $existingProfil?->target_sks,
            'foto_profil' => $existingProfil?->foto_profil,
        ], $validated);

        $shouldDeletePhoto = (bool) ($validated['hapus_foto_profil'] ?? false);
        if ($shouldDeletePhoto && $existingProfil?->foto_profil) {
            Storage::disk('public')->delete($existingProfil->foto_profil);
            $profileData['foto_profil'] = null;
        }

        if ($request->hasFile('foto_profil')) {
            $fotoPath = $request->file('foto_profil')->store('avatars', 'public');
            if ($existingProfil?->foto_profil) {
                Storage::disk('public')->delete($existingProfil->foto_profil);
            }
            $profileData['foto_profil'] = $fotoPath;
        }

        unset($profileData['hapus_foto_profil']);

        DB::transaction(function () use ($user, $userData, $profileData) {
            if ($userData !== []) {
                $user->fill($userData);
                $user->save();
            }

            Profil::updateOrCreate(['user_id' => $user->id], $profileData);
        });

        app(AuditLogService::class)->record(
            $request,
            'api_update_profile',
            'Memperbarui profil via API: ' . $user->name,
            $user->id,
        );

        $profil = $user->fresh(['profil'])->profil;

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil disimpan',
            'data' => [
                'user' => $user->fresh(['profil']),
                'profil' => $profil,
            ],
        ]);
    }
}
