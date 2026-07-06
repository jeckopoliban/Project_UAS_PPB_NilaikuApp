@extends('portal.layout')

@section('title', 'Dashboard')
@section('header', 'Dashboard Portal')

@section('content')
    @php
        $progressWidth = isset($total_sks, $target_sks) ? min(100, round(($total_sks / $target_sks) * 100)) : 82;
        $chartLabels = isset($ip_trend) ? array_column($ip_trend, 'nama_semester') : ['S1', 'S2', 'S3', 'S4'];
        $chartData = isset($ip_trend) ? array_column($ip_trend, 'nilai_ip') : [3.42, 3.61, 3.75, 3.81];
    @endphp
    <div class="space-y-6">
        <div class="container-max">
            <div class="mb-4">
                <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_220px] lg:items-end">
                    <div>
                        <h1 class="text-4xl font-bold text-text-heading">Halo, {{ explode(' ', auth()->user()->name)[0] }}! 👋</h1>
                        <p class="mt-2 text-sm text-text-body">Berikut ringkasan akademik Anda hari ini.</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-text-muted">{{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="rounded-app-card border border-border-subtle bg-white p-6 shadow-app-soft">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-text-label">IPK</p>
                        <p class="mt-3 text-3xl font-semibold text-text-heading">{{ isset($ipk_kumulatif) ? number_format($ipk_kumulatif,2) : '3.81' }}</p>
                    </div>
                    <span class="inline-flex rounded-app-pill bg-brand-blue-light/15 px-3 py-1 text-xs font-semibold text-brand-blue">Statis</span>
                </div>
            </div>
            <div class="rounded-app-card border border-border-subtle bg-white p-6 shadow-app-soft">
                <div>
                    <p class="text-sm font-medium text-text-label">Total SKS</p>
                    <p class="mt-3 text-3xl font-semibold text-text-heading">{{ $total_sks ?? 124 }}</p>
                </div>
            </div>
            <div class="rounded-app-card border border-border-subtle bg-white p-6 shadow-app-soft">
                <div>
                    <p class="text-sm font-medium text-text-label">Semester Aktif</p>
                    <p class="mt-3 text-3xl font-semibold text-text-heading">{{ $total_semester ?? 0 }}</p>
                </div>
            </div>
            <div class="rounded-app-card border border-border-subtle bg-white p-6 shadow-app-soft">
                <div>
                    <p class="text-sm font-medium text-text-label">Target IPK</p>
                    <p class="mt-3 text-3xl font-semibold text-text-heading">{{ isset($target_ipk) ? number_format($target_ipk,2) : '3.80' }}</p>
                    <p class="mt-2 text-sm text-brand-teal">Target akademik Anda</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 rounded-app-card border border-border-subtle bg-white p-6 shadow-app-soft">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-text-heading">Grafik Perkembangan IP</h2>
                        <p class="mt-1 text-sm text-text-label">Lihat tren IP per semester dan progress akademik.</p>
                    </div>
                    <span class="inline-flex rounded-app-pill bg-brand-teal/10 px-3 py-1 text-xs font-semibold text-brand-teal">Terbaru</span>
                </div>
                <div class="mt-5 h-72">
                    <canvas id="ipChart" class="w-full h-full"></canvas>
                </div>
            </div>

            <div class="space-y-4">
                <div class="rounded-app-card border border-border-subtle bg-white p-6 shadow-app-soft">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-sm font-medium text-text-label">Progress SKS</h3>
                            <p class="mt-3 text-xl font-semibold text-text-heading">{{ $total_sks ?? 124 }} / {{ $target_sks ?? 144 }} SKS</p>
                        </div>
                        <span class="inline-flex rounded-app-pill bg-brand-blue-light/15 px-3 py-1 text-xs font-semibold text-brand-blue">{{ $progressWidth }}%</span>
                    </div>
                    <div class="mt-5 rounded-full bg-slate-100 p-1">
                        <div id="progress-sks-bar" class="h-3 rounded-full bg-gradient-button" data-progress="{{ $progressWidth }}"></div>
                    </div>
                </div>

                <div class="rounded-app-card border border-border-subtle bg-white p-6 shadow-app-soft">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-sm font-medium text-text-label">Reminder</h3>
                            <p class="mt-1 text-sm text-text-body">Tindakan prioritas untuk menjaga IP tetap stabil.</p>
                        </div>
                        <span class="inline-flex rounded-app-pill bg-warning-amber/10 px-3 py-1 text-xs font-semibold text-warning-amber">Penting</span>
                    </div>
                    <ul class="mt-4 space-y-3 text-sm text-text-heading">
                        @foreach($reminders as $reminder)
                            @php
                                $isSuccess = str_starts_with($reminder, 'Seluruh data akademik sudah lengkap');
                            @endphp
                            <li class="flex items-center gap-2">
                                @if($isSuccess)
                                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-brand-teal/10 text-brand-teal">✓</span>
                                @else
                                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-warning-amber/10 text-warning-amber">⚠</span>
                                @endif
                                {{ $reminder }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="rounded-app-card border border-border-subtle bg-white p-6 shadow-app-soft">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-sm font-medium text-text-label">Quick Actions</h3>
                            <p class="mt-1 text-sm text-text-body">Akses cepat untuk halaman penting.</p>
                        </div>
                    </div>
                    <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <a href="{{ route('portal.tahun-akademik.create') }}" class="inline-flex items-center justify-center rounded-app-pill bg-gradient-button px-4 py-3 text-sm font-semibold text-white transition hover:bg-brand-blue-light">+ Tambah Semester</a>
                        <a href="{{ route('portal.mata-kuliah.create') }}" class="inline-flex items-center justify-center rounded-app-pill bg-gradient-button px-4 py-3 text-sm font-semibold text-white transition hover:bg-brand-blue-light">+ Tambah Mata Kuliah</a>
                        <a href="{{ route('portal.nilai-saya') }}" class="inline-flex items-center justify-center rounded-app-pill border border-border-subtle bg-white px-4 py-3 text-sm font-semibold text-text-body transition hover:bg-sidebar-active">Nilai Saya</a>
                        <a href="{{ route('portal.nilai.ips-ipk') }}" class="inline-flex items-center justify-center rounded-app-pill border border-border-subtle bg-white px-4 py-3 text-sm font-semibold text-text-body transition hover:bg-sidebar-active">Lihat IP/IPK</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const progressBar = document.getElementById('progress-sks-bar');
        if (progressBar) {
            progressBar.style.width = `${progressBar.dataset.progress}%`;
        }

        const labels = <?php echo json_encode(isset($ip_trend) ? array_column($ip_trend, 'nama_semester') : ['S1', 'S2', 'S3', 'S4']); ?>;
        const data = <?php echo json_encode(isset($ip_trend) ? array_column($ip_trend, 'nilai_ip') : [3.42, 3.61, 3.75, 3.81]); ?>;
        const ctx = document.getElementById('ipChart').getContext('2d');
        const gradientFill = ctx.createLinearGradient(0, 0, 0, 400);
        gradientFill.addColorStop(0, 'rgba(58, 126, 248, 0.35)');
        gradientFill.addColorStop(1, 'rgba(37, 99, 235, 0.02)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'IP',
                    data,
                    borderColor: '#2563EB',
                    backgroundColor: gradientFill,
                    fill: true,
                    tension: 0.36,
                    pointBackgroundColor: '#2563EB',
                    pointBorderColor: '#ffffff',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                }],
            },
            options: {
                plugins: {
                    legend: {display: false},
                },
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        grid: {display: false},
                        ticks: {color: '#6b7280'},
                    },
                    y: {
                        min: 0,
                        max: 4,
                        ticks: {
                            stepSize: 0.5,
                            callback: (value) => value.toFixed(2),
                            color: '#6b7280',
                        },
                        grid: {color: 'rgba(148, 163, 184, 0.16)'},
                    },
                },
            },
        });
    </script>
@endsection
