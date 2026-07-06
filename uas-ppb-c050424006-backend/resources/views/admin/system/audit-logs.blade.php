@extends('admin.layout')

@section('title', 'Audit Logs')
@section('header', 'Audit Logs')

@section('content')
<div class="bg-white rounded-app-card border border-border-subtle overflow-hidden shadow-app-card">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-border-subtle bg-slate-50">
                    <th class="px-4 py-3 text-left text-text-muted">User</th>
                    <th class="px-4 py-3 text-left text-text-muted">Aksi</th>
                    <th class="px-4 py-3 text-left text-text-muted">Deskripsi</th>
                    <th class="px-4 py-3 text-left text-text-muted">IP Address</th>
                    <th class="px-4 py-3 text-left text-text-muted">Waktu</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-subtle">
                @forelse ($logs as $log)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3 text-text-heading">{{ $log->user?->name ?? 'N/A' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-app-pill bg-slate-100 px-2 py-1 text-xs font-semibold text-text-body">{{ $log->aksi }}</span>
                        </td>
                        <td class="px-4 py-3 text-text-muted text-xs">{{ $log->deskripsi }}</td>
                        <td class="px-4 py-3 text-text-muted text-xs">{{ $log->ip_address }}</td>
                        <td class="px-4 py-3 text-text-muted text-xs">{{ $log->created_at?->format('d/m/Y H:i') ?? 'N/A' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-text-muted">Belum ada audit logs</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4 border-t border-border-subtle">
        {{ $logs->links() }}
    </div>
</div>
@endsection

