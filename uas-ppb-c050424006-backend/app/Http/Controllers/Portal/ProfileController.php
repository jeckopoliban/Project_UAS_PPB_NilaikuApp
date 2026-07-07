<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AktivitasLog;
use App\Models\Profil;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $user->load('profil');

        return view('portal.profil.show', [
            'user' => $user,
        ]);
    }

    public function edit(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $user->load('profil');

        return view('portal.profil.edit', [
            'user' => $user,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'nim_nis' => ['nullable', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:255'],
            'nama_institusi' => ['nullable', 'string', 'max:255'],
            'jenis_institusi' => ['nullable', 'string', 'in:perguruan_tinggi,sekolah'],
            'program_studi' => ['nullable', 'string', 'max:255'],
            'target_sks' => ['nullable', 'integer', 'between:0,200'],
            'foto_profil' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        $user->fill([
            'name' => $validated['name'],
        ]);

        $user->save();

        $profileData = collect($validated)->only([
            'nim_nis',
            'no_hp',
            'nama_institusi',
            'jenis_institusi',
            'program_studi',
            'target_sks',
        ])->toArray();

        if ($request->hasFile('foto_profil')) {
            $fotoPath = $request->file('foto_profil')->store('avatars', 'public');
            if ($user->profil?->foto_profil) {
                Storage::disk('public')->delete($user->profil->foto_profil);
            }
            $profileData['foto_profil'] = $fotoPath;
        }

        Profil::updateOrCreate(
            ['user_id' => $user->id],
            $profileData
        );

        app(AuditLogService::class)->record(
            $request,
            'update_profil_mahasiswa',
            'Memperbarui profil mahasiswa: ' . $user->name,
            $user->id,
        );

        AktivitasLog::create([
            'mahasiswa_id' => $user->id,
            'aksi' => 'Memperbarui Profil',
            'deskripsi' => 'Profil akademik diperbarui melalui portal',
        ]);

        return redirect()->route('portal.profil.show')->with('success', 'Profil berhasil diperbarui.');
    }
}
