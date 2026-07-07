<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        app(AuditLogService::class)->record(
            $request,
            'login',
            'Login berhasil: ' . $request->user()?->name,
            $request->user()?->id,
        );

        // Redirect to the central `dashboard` route which forwards to the
        // appropriate admin or portal dashboard based on user role.
        return redirect()->route('dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $currentUser = $request->user();

        app(AuditLogService::class)->record(
            $request,
            'logout',
            'Logout: ' . ($currentUser?->name ?? '-'),
            $currentUser?->id,
        );

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
