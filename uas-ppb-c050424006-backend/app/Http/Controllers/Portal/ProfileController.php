<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\AktivitasLog;
use App\Models\Profil;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $validated = $request->validated();

        $user->fill([ 
            'name' => $validated['name'], 
            'email' => $validated['email'],
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

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

        AktivitasLog::create([
            'mahasiswa_id' => $user->id,
            'aksi' => 'Memperbarui Profil',
            'deskripsi' => 'Profil akademik diperbarui melalui portal',
        ]);

        return redirect()->route('portal.profil.show')->with('success', 'Profil berhasil diperbarui.');
    }
}
