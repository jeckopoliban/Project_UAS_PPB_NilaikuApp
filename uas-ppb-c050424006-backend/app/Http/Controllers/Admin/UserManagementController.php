<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\KomponenNilai;
use App\Models\MataKuliah;
use App\Models\TahunAkademik;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(): View
    {
        $users = User::paginate(15);

        return view('admin.user-management.index', ['users' => $users]);
    }

    public function toggleActive($id): RedirectResponse
    {
        $user = User::findOrFail($id);
        $user->status_aktif = ! $user->status_aktif;
        $user->save();

        app(AuditLogService::class)->record(
            request(),
            'toggle_active_user',
            ($user->status_aktif ? 'Mengaktifkan' : 'Menonaktifkan') . " user: {$user->id} ({$user->name})",
        );

        $message = $user->status_aktif ? 'Akun diaktifkan' : 'Akun dinonaktifkan';

        return redirect()->back()->with('success', $message);
    }

    public function showReadOnly(Request $request, $id): View
    {
        $user = User::findOrFail($id);
        $currentUser = $request->user();

        // Audit log
        AuditLog::create([
            'user_id' => $currentUser?->id,
            'aksi' => 'lihat_data_user',
            'deskripsi' => "Melihat data user: {$user->id} ({$user->name})",
            'ip_address' => request()->ip(),
        ]);

        $tahunAkademiks = TahunAkademik::where('mahasiswa_id', $id)->get();
        $mataKuliahs = MataKuliah::where('mahasiswa_id', $id)->get();
        $komponenNilais = KomponenNilai::where('mahasiswa_id', $id)->get();

        return view('admin.user-management.show', [
            'user' => $user,
            'tahunAkademiks' => $tahunAkademiks,
            'mataKuliahs' => $mataKuliahs,
            'komponenNilais' => $komponenNilais,
        ]);
    }

    public function destroy(Request $request, $id): RedirectResponse
    {
        $currentUser = $request->user();

        if ($id == $currentUser?->id) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri');
        }

        $user = User::findOrFail($id);
        $user->delete();

        app(AuditLogService::class)->record(
            $request,
            'hapus_user',
            "Menghapus user: {$user->name} ({$user->email})",
            $currentUser?->id,
        );

        return redirect()->route('admin.user-management.index')->with('success', 'User berhasil dihapus');
    }

    public function trashed(): View
    {
        $users = User::onlyTrashed()->paginate(15);

        return view('admin.user-management.trashed', ['users' => $users]);
    }

    public function restore(Request $request, $id): RedirectResponse
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        app(AuditLogService::class)->record(
            $request,
            'pulihkan_user',
            "Memulihkan user: {$user->name} ({$user->email})",
            $request->user()?->id,
        );

        return redirect()->route('admin.users.trashed')->with('success', 'User berhasil dipulihkan');
    }

    public function confirmForceDelete($id): View
    {
        $user = User::onlyTrashed()->findOrFail($id);

        return view('admin.user-management.force-delete-confirm', ['user' => $user]);
    }

    public function forceDelete(Request $request, $id): RedirectResponse
    {
        $user = User::onlyTrashed()->findOrFail($id);

        if ($user->profil) {
            $user->profil->delete();
        }

        KomponenNilai::where('mahasiswa_id', $user->id)->delete();
        MataKuliah::where('mahasiswa_id', $user->id)->delete();
        TahunAkademik::where('mahasiswa_id', $user->id)->delete();

        $user->forceDelete();

        app(AuditLogService::class)->record(
            $request,
            'hapus_permanen_user',
            "Menghapus permanen user: {$user->name} ({$user->email})",
            $request->user()?->id,
        );

        return redirect()->route('admin.users.trashed')->with('success', 'User berhasil dihapus permanen');
    }
}
