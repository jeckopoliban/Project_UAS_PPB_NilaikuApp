<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\AktivitasLog;
use App\Models\Profil;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->safe()->only(['name', 'email']));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $profileData = $request->safe()->only([
            'nim_nis',
            'no_hp',
            'nama_institusi',
            'jenis_institusi',
            'program_studi',
            'target_ipk',
            'target_sks',
        ]);

        if ($request->hasFile('foto_profil')) {
            $fotoPath = $request->file('foto_profil')->store('avatars', 'public');
            if ($user->profil?->foto_profil) {
                Storage::disk('public')->delete($user->profil->foto_profil);
            }
            $profileData['foto_profil'] = $fotoPath;
        }

        // Ensure DB non-nullable columns are always present when creating.
        $profileData = array_merge([
            'nama_institusi' => $user->profil->nama_institusi ?? '',
            'jenis_institusi' => $user->profil->jenis_institusi ?? 'perguruan_tinggi',
        ], $profileData);

        Profil::updateOrCreate([
            'user_id' => $user->id,
        ], $profileData);

        AktivitasLog::create([
            'mahasiswa_id' => $user->id,
            'aksi' => 'Memperbarui Profil',
            'deskripsi' => 'Profil akademik diperbarui',
        ]);

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        // In this application users are soft-deletable, but for account
        // self-deletion we perform a permanent delete to fully remove records
        // as tests expect.
        $user->forceDelete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
