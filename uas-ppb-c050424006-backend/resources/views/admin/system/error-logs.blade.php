@extends('admin.layout')

@section('title', 'Error Logs')
@section('header', 'Error Logs')

@section('content')
<div class="bg-white rounded-app-card border border-border-subtle p-6 shadow-app-card">
    <div class="mb-4">
        <form method="POST" action="{{ route('admin.system.toggle-maintenance') }}" class="inline">
            @csrf
            <button type="submit" class="inline-flex items-center justify-center rounded-app-pill bg-yellow-100 px-4 py-2 text-sm font-semibold text-yellow-800 transition hover:bg-yellow-200">Toggle Maintenance Mode</button>
        </form>
    </div>

    <div class="bg-slate-50 rounded-app-card p-4 font-mono text-xs text-text-body overflow-auto max-h-96">
        @if (count($lines) > 0)
            @foreach ($lines as $line)
                @if (trim($line))
                    <div>{{ $line }}</div>
                @endif
            @endforeach
        @else
            <div class="text-text-muted">Tidak ada error logs</div>
        @endif
    </div>
</div>
@endsection

