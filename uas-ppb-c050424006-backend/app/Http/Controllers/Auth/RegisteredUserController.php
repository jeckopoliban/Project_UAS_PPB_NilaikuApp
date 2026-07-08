<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Profil;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'nama_institusi' => ['required', 'string', 'max:255'],
            'jenis_institusi' => ['required', 'string', 'in:perguruan_tinggi,sekolah'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Profil::create([
            'user_id' => $user->id,
            'nama_institusi' => $request->nama_institusi,
            'jenis_institusi' => $request->jenis_institusi,
        ]);

        event(new Registered($user));

        app(AuditLogService::class)->record(
            $request,
            'register_user',
            "Mendaftar akun baru: {$user->id} ({$user->name})",
            $user->id,
        );

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
