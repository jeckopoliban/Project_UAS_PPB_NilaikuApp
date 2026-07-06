@extends('admin.layout')

@section('title', 'Institusi Referensi')
@section('header', 'Institusi Referensi')

@section('content')
<div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-6">
    <h3 class="text-xl font-semibold text-text-heading">Daftar Institusi</h3>
    <a href="{{ route('admin.institusi-referensi.create') }}" class="inline-flex items-center justify-center rounded-app-pill bg-gradient-button px-4 py-2 text-sm font-semibold text-white shadow-app-soft transition hover:opacity-95">+ Tambah Institusi</a>
</div>

<div class="bg-white rounded-app-card border border-border-subtle overflow-hidden shadow-app-card">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-border-subtle bg-slate-50">
                    <th class="px-4 py-3 text-left text-text-muted">Nama Institusi</th>
                    <th class="px-4 py-3 text-left text-text-muted">Jenis</th>
                    <th class="px-4 py-3 text-left text-text-muted w-[200px]">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-subtle">
                @forelse ($items as $item)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3 text-text-heading">{{ $item->nama_institusi }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-app-pill bg-slate-100 px-2 py-1 text-xs font-semibold text-text-body capitalize">{{ str_replace('_', ' ', $item->jenis) }}</span>
                        </td>
                        <td class="px-4 py-3 w-[200px] flex flex-wrap items-center gap-2 justify-start">
                            <a href="{{ route('admin.institusi-referensi.edit', $item->id) }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-app-pill bg-slate-100 px-3 py-1 text-xs font-semibold text-text-heading transition hover:bg-slate-200">Edit</a>
                            <form method="POST" action="{{ route('admin.institusi-referensi.destroy', $item->id) }}" class="inline" onsubmit="return confirm('Yakin ingin menghapus?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center justify-center whitespace-nowrap rounded-app-pill bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-600 transition hover:bg-rose-100">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-text-muted">Belum ada institusi</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4 border-t border-border-subtle">
        {{ $items->links() }}
    </div>
</div>
@endsection
