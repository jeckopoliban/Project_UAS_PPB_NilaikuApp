<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\KomponenNilai;
use App\Models\MataKuliah;
use App\Models\TahunAkademik;
use App\Models\User;
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

        AuditLog::create([
            'user_id' => $currentUser?->id,
            'aksi' => 'hapus_user',
            'deskripsi' => "Menghapus user: {$user->name} ({$user->email})",
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);

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

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'aksi' => 'pulihkan_user',
            'deskripsi' => "Memulihkan user: {$user->name} ({$user->email})",
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);

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

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'aksi' => 'hapus_permanen_user',
            'deskripsi' => "Menghapus permanen user: {$user->name} ({$user->email})",
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);

        return redirect()->route('admin.users.trashed')->with('success', 'User berhasil dihapus permanen');
    }
}
