@extends('admin.layout')

@section('title', 'User Management')
@section('header', 'User Management')

@section('content')
<div class="bg-white rounded-app-card border border-border-subtle p-6 shadow-app-card">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-3">
            <div>
                <h3 class="text-lg font-semibold text-text-heading">Daftar User</h3>
                <p class="text-sm text-text-body">Kelola akun dan status user dalam sistem.</p>
            </div>
            <a href="{{ route('admin.users.trashed') }}" class="inline-flex items-center justify-center rounded-app-pill border border-rose-200 bg-white px-4 py-2 text-sm font-semibold text-rose-600 transition hover:bg-rose-50">Lihat User Terhapus</a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm table-auto">
            <thead>
                <tr class="border-b border-border-subtle">
                    <th class="px-4 py-3 text-left text-text-muted">Nama</th>
                    <th class="px-4 py-3 text-left text-text-muted">Email</th>
                    <th class="px-4 py-3 text-left text-text-muted">Role</th>
                    <th class="px-4 py-3 text-left text-text-muted">Status</th>
                    <th class="px-4 py-3 text-left text-text-muted w-[240px]">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-subtle">
                @forelse ($users as $user)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3 text-text-heading">{{ $user->name }}</td>
                        <td class="px-4 py-3 text-text-body">{{ $user->email }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-app-pill bg-slate-100 px-2 py-1 text-xs font-semibold text-text-body">{{ $user->role }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-app-pill px-2 py-1 text-xs font-semibold {{ $user->status_aktif ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">{{ $user->status_aktif ? 'Aktif' : 'Nonaktif' }}</span>
                        </td>
                        <td class="px-4 py-4 w-[280px] min-w-[280px]">
                            <div class="flex flex-nowrap items-center gap-2">
                                <a href="{{ route('admin.user-management.show', $user->id) }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-app-pill bg-slate-100 px-2.5 py-1 text-xs font-semibold text-text-heading transition hover:bg-slate-200">Lihat Data</a>
                                <form method="POST" action="{{ route('admin.user-management.toggle-active', $user->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center justify-center whitespace-nowrap rounded-app-pill bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 transition hover:bg-amber-100">
                                        {{ $user->status_aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" class="inline" onsubmit="return confirm('Yakin ingin menghapus user ini? Aksi ini akan menonaktifkan akun tetapi tidak menghapus data akademik.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center justify-center whitespace-nowrap rounded-app-pill bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-600 transition hover:bg-rose-100">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-text-muted">Belum ada user</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $users->links() }}
    </div>
</div>
@endsection
