@extends('admin.layout')

@section('title', 'Dashboard')
@section('header', 'System Dashboard')

@section('subheader', 'Ringkasan sistem dan aktivitas admin hari ini.')

@section('content')
@php
    $activeUserProgress = (int) max(0, min(100, $activeUserPercent));
@endphp
<div class="bg-white rounded-app-card border border-border-subtle p-6 mb-8">
    <p id="dashboard-local-line" class="text-sm text-text-body whitespace-nowrap">Memuat tanggal & waktu...</p>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
    <div class="bg-white rounded-app-card border border-border-subtle p-5 shadow-app-card">
        <div class="flex items-start gap-3">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-blue-50 text-brand-blue">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14.5c-4.5 0-8 2.25-8 4.5V22h16v-3c0-2.25-3.5-4.5-8-4.5z" />
                </svg>
            </span>
            <div>
                <p class="text-sm text-text-label">Total Users</p>
                <p class="mt-3 text-2xl font-semibold text-text-heading">{{ $totalUsers }}</p>
                <p class="mt-2 text-xs text-emerald-600">+{{ $weeklyNewUsers }} minggu ini</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-app-card border border-border-subtle p-5 shadow-app-card">
        <div class="flex items-start gap-3">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-blue-50 text-brand-blue">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v8m4-4H8" />
                </svg>
            </span>
            <div class="flex-1">
                <p class="text-sm text-text-label">Active Users</p>
                <p class="mt-3 text-2xl font-semibold text-text-heading">{{ $activeUsers }}</p>
                <div class="mt-4 rounded-full bg-slate-100 p-1">
                    <div id="active-users-progress" class="h-2 rounded-full bg-gradient-button" data-progress="{{ $activeUserProgress }}"></div>
                </div>
                <p class="mt-2 text-xs text-text-muted">{{ $activeUserPercent }}% dari total user aktif</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-app-card border border-border-subtle p-5 shadow-app-card">
        <div class="flex items-start gap-3">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-emerald-50 text-emerald-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v8m-4-4h8" />
                </svg>
            </span>
            <div>
                <p class="text-sm text-text-label">Active Semesters</p>
                <p class="mt-3 text-2xl font-semibold text-text-heading">{{ $activeSemesters ?? 0 }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-app-card border border-border-subtle p-5 shadow-app-card">
        <div class="flex items-start gap-3">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-amber-50 text-amber-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </span>
            <div>
                <p class="text-sm text-text-label">Global Grading Templates</p>
                <p class="mt-3 text-2xl font-semibold text-text-heading">{{ $gradingTemplateCount ?? 0 }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-app-card border border-border-subtle p-5 shadow-app-card">
        <div class="flex items-start gap-3">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-rose-50 text-rose-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 4.5h15v15h-15z" />
                </svg>
            </span>
            <div>
                <p class="text-sm text-text-label">Total Audit Logs</p>
                <p class="mt-3 text-2xl font-semibold text-text-heading">{{ $auditLogCount ?? 0 }}</p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-app-card border border-border-subtle p-6 shadow-app-card">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg font-semibold text-text-heading">Pertumbuhan User</h3>
                <p class="text-sm text-text-body">Jumlah user baru per bulan selama 6 bulan terakhir.</p>
            </div>
            <span class="inline-flex rounded-app-pill bg-brand-blue-light/15 px-3 py-1 text-xs font-semibold text-brand-blue">Chart</span>
        </div>
        <div class="mt-6 h-[240px]">
            <canvas id="userGrowthChart" class="w-full h-full"></canvas>
        </div>
        <div id="user-growth-data" class="hidden">
            @forelse ($userGrowth as $item)
                <span data-growth-month="{{ data_get($item, 'month') }}" data-growth-count="{{ data_get($item, 'count') }}"></span>
            @empty
                <span data-growth-month="Jan" data-growth-count="0"></span>
                <span data-growth-month="Feb" data-growth-count="0"></span>
                <span data-growth-month="Mar" data-growth-count="0"></span>
                <span data-growth-month="Apr" data-growth-count="0"></span>
                <span data-growth-month="Mei" data-growth-count="0"></span>
                <span data-growth-month="Jun" data-growth-count="0"></span>
            @endforelse
        </div>
    </div>
    <div class="bg-white rounded-app-card border border-border-subtle p-6 shadow-app-card">
        <div class="flex items-center justify-between gap-4 mb-4">
            <div>
                <h3 class="text-lg font-semibold text-text-heading">Top Institusi</h3>
                <p class="text-sm text-text-body">Ringkasan institusi teratas.</p>
            </div>
        </div>
        <ul class="space-y-3">
            @forelse ($topInstitusi as $item)
                <li class="flex items-center justify-between text-sm">
                    <span class="text-text-body">{{ $item->nama_institusi }}</span>
                    <span class="text-text-heading font-semibold">{{ $item->count }}</span>
                </li>
            @empty
                <li class="text-text-label">Belum ada data</li>
            @endforelse
        </ul>
    </div>
    <div class="bg-white rounded-app-card border border-border-subtle p-6 shadow-app-card">
        <div class="flex items-center justify-between gap-4 mb-4">
            <div>
                <h3 class="text-lg font-semibold text-text-heading">Top Mata Kuliah</h3>
                <p class="text-sm text-text-body">Ringkasan mata kuliah teratas.</p>
            </div>
        </div>
        <ul class="space-y-3">
            @forelse ($topMataKuliah as $item)
                <li class="flex items-center justify-between text-sm">
                    <span class="text-text-body">{{ $item->nama_mk }}</span>
                    <span class="text-text-heading font-semibold">{{ $item->count }}</span>
                </li>
            @empty
                <li class="text-text-label">Belum ada data</li>
            @endforelse
        </ul>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function formatIndonesianDate(date) {
        const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
        return new Intl.DateTimeFormat('id-ID', options).format(date);
    }

    function getLocalDateTimeLabel(date) {
        const formattedDate = formatIndonesianDate(date);
        const hour = date.getHours().toString().padStart(2, '0');
        const minute = date.getMinutes().toString().padStart(2, '0');
        const tz = date.toLocaleTimeString('id-ID', { timeZoneName: 'short' }).split(' ').pop();
        return `${formattedDate} • ${hour}:${minute} ${tz}`;
    }

    document.addEventListener('DOMContentLoaded', function () {
        const lineEl = document.getElementById('dashboard-local-line');
        const now = new Date();

        if (lineEl) {
            lineEl.textContent = getLocalDateTimeLabel(now);
        }

        const progressBar = document.getElementById('active-users-progress');
        if (progressBar) {
            progressBar.style.width = `${progressBar.dataset.progress}%`;
        }

        const growthItems = Array.from(document.querySelectorAll('#user-growth-data [data-growth-month]'));
        const chartLabels = growthItems.map((item) => item.dataset.growthMonth || '');
        const chartData = growthItems.map((item) => Number(item.dataset.growthCount || 0));
        const ctx = document.getElementById('userGrowthChart').getContext('2d');
        const gradientFill = ctx.createLinearGradient(0, 0, 0, 300);
        gradientFill.addColorStop(0, 'rgba(59, 130, 246, 0.3)');
        gradientFill.addColorStop(1, 'rgba(59, 130, 246, 0.05)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'User Baru',
                    data: chartData,
                    borderColor: '#2563EB',
                    backgroundColor: gradientFill,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#2563EB',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                }],
            },
            options: {
                plugins: {
                    legend: { display: false },
                },
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { 
                            color: '#6b7280',
                            maxRotation: 45,
                            minRotation: 45,
                            font: { size: 11 }
                        },
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(148, 163, 184, 0.16)' },
                        ticks: {
                            color: '#6b7280',
                            precision: 0,
                            stepSize: 1,
                        },
                    },
                },
            },
        });
    });
</script>
@endsection

