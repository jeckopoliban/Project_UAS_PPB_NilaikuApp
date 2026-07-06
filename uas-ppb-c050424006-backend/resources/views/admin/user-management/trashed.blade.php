@extends('admin.layout')

@section('title', 'User Terhapus')
@section('header', 'User Terhapus')

@section('content')
<div class="bg-white rounded-app-card border border-border-subtle p-6 shadow-app-card">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-3">
            <div>
                <h3 class="text-lg font-semibold text-text-heading">Daftar User Terhapus</h3>
                <p class="text-text-body text-sm">User yang dihapus hanya soft delete. Anda bisa memulihkan atau menghapus permanen.</p>
            </div>
            <a href="{{ route('admin.user-management.index') }}" class="inline-flex items-center justify-center rounded-app-pill border border-border-subtle bg-white px-4 py-2 text-sm font-semibold text-text-heading transition hover:bg-slate-50">Kembali ke Daftar Aktif</a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-border-subtle">
                    <th class="px-4 py-3 text-left text-text-muted">Nama</th>
                    <th class="px-4 py-3 text-left text-text-muted">Email</th>
                    <th class="px-4 py-3 text-left text-text-muted">Dihapus Pada</th>
                    <th class="px-4 py-3 text-right text-text-muted">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-subtle">
                @forelse ($users as $user)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3 text-text-heading">{{ $user->name }}</td>
                        <td class="px-4 py-3 text-text-body">{{ $user->email }}</td>
                        <td class="px-4 py-3 text-text-muted">{{ $user->deleted_at?->format('d M Y H:i') }}</td>
                        <td class="px-4 py-3 min-w-[180px] flex flex-wrap items-center gap-2 justify-end">
                            <form method="POST" action="{{ route('admin.users.restore', $user->id) }}" class="inline">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="inline-flex items-center justify-center whitespace-nowrap rounded-app-pill bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100">Pulihkan</button>
                            </form>
                            <a href="{{ route('admin.users.force-delete.confirm', $user->id) }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-app-pill bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-600 transition hover:bg-rose-100">Hapus Permanen</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-text-muted">Belum ada user terhapus</td>
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
