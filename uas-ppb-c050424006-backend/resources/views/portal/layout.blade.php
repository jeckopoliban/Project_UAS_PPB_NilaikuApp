<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php $title = trim($__env->yieldContent('title')) ?: 'Dashboard'; @endphp
    <title>{{ $title }} - Nilaiku</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --primary-start: #2f6fed;
            --primary-end: #3ec9c0;
            --sidebar-active: #eff3fb;
            --text-heading: #111827;
            --text-label: #6b7280;
            --text-body: #4b5563;
        }

        .rounded-app-btn {
            border-radius: 18px;
        }

        .rounded-app-card {
            border-radius: 28px;
        }

        .rounded-app-input {
            border-radius: 18px;
        }

        .rounded-app-pill {
            border-radius: 9999px;
        }

        .shadow-app-soft {
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
        }

        .bg-sidebar-active {
            background: var(--sidebar-active);
        }

        .text-text-heading {
            color: var(--text-heading);
        }

        .text-text-label {
            color: var(--text-label);
        }

        .text-text-body {
            color: var(--text-body);
        }
    </style>
</head>
<body class="bg-bg-page text-text-body font-sans min-h-screen">
    <div class="flex min-h-screen">
        <aside class="hidden lg:flex w-64 flex-col bg-white border-r border-border-subtle">
            <div class="px-6 py-8 border-b border-border-subtle">
                <div class="inline-flex items-center gap-3">
                    <span class="text-2xl">📘</span>
                    <div>
                        <p class="text-sm uppercase tracking-[0.2em] text-text-label">NilaiKu Academic</p>
                    </div>
                </div>
            </div>

            <nav class="mt-4 flex-1 space-y-1 px-2 py-4">
                <a href="{{ route('portal.dashboard') }}" class="flex items-center gap-3 rounded-app-btn px-4 py-3 text-sm font-medium transition {{ Route::currentRouteName() === 'portal.dashboard' ? 'bg-sidebar-active text-text-heading' : 'text-text-body hover:bg-sidebar-active' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9.75L12 3l9 6.75v8.25a1.5 1.5 0 01-1.5 1.5H4.5A1.5 1.5 0 013 18V9.75z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 22V12h6v10" />
                    </svg>
                    Dashboard
                </a>
                <a href="{{ route('portal.tahun-akademik.index') }}" class="flex items-center gap-3 rounded-app-btn px-4 py-3 text-sm font-medium transition {{ str_starts_with(Route::currentRouteName(), 'portal.tahun-akademik') ? 'bg-sidebar-active text-text-heading' : 'text-text-body hover:bg-sidebar-active' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 7.5V5.25M15.75 7.5V5.25M4.5 9.75h15a.75.75 0 01.75.75v9a.75.75 0 01-.75.75h-15a.75.75 0 01-.75-.75v-9a.75.75 0 01.75-.75z" />
                    </svg>
                    Tahun Akademik
                </a>
                <a href="{{ route('portal.mata-kuliah.index') }}" class="flex items-center gap-3 rounded-app-btn px-4 py-3 text-sm font-medium transition {{ str_starts_with(Route::currentRouteName(), 'portal.mata-kuliah') ? 'bg-sidebar-active text-text-heading' : 'text-text-body hover:bg-sidebar-active' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 6.75h15a.75.75 0 01.75.75v9a.75.75 0 01-.75.75h-15a.75.75 0 01-.75-.75v-9a.75.75 0 01.75-.75z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12h15" />
                    </svg>
                    Mata Kuliah
                </a>
                <a href="{{ route('portal.nilai-saya') }}" class="flex items-center gap-3 rounded-app-btn px-4 py-3 text-sm font-medium transition {{ Route::currentRouteName() === 'portal.nilai-saya' ? 'bg-sidebar-active text-text-heading' : 'text-text-body hover:bg-sidebar-active' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 7.5h15M4.5 12h15M4.5 16.5h15" />
                    </svg>
                    Nilai Saya
                </a>
                <a href="{{ route('portal.rekapitulasi') }}" class="flex items-center gap-3 rounded-app-btn px-4 py-3 text-sm font-medium transition {{ Route::currentRouteName() === 'portal.rekapitulasi' ? 'bg-sidebar-active text-text-heading' : 'text-text-body hover:bg-sidebar-active' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6M7 6h10M5 19h14a1 1 0 001-1V6a1 1 0 00-1-1H5a1 1 0 00-1 1v12a1 1 0 001 1z" />
                    </svg>
                    Rekapitulasi Nilai
                </a>
                <a href="{{ route('portal.grading') }}" class="flex items-center gap-3 rounded-app-btn px-4 py-3 text-sm font-medium transition {{ str_starts_with(Route::currentRouteName(), 'portal.grading') ? 'bg-sidebar-active text-text-heading' : 'text-text-body hover:bg-sidebar-active' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v8m0 0l3-3m-3 3l-3-3" />
                    </svg>
                    Grading
                </a>
                <a href="{{ route('portal.nilai.ips-ipk') }}" class="flex items-center gap-3 rounded-app-btn px-4 py-3 text-sm font-medium transition {{ str_starts_with(Route::currentRouteName(), 'portal.nilai') ? 'bg-sidebar-active text-text-heading' : 'text-text-body hover:bg-sidebar-active' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 16.5l3-3 2.25 2.25 4.5-4.5 4.5 4.5" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.75v10.5" />
                    </svg>
                    IP/IPK
                </a>
                <a href="{{ route('portal.profil.show') }}" class="flex items-center gap-3 rounded-app-btn px-4 py-3 text-sm font-medium transition {{ Route::currentRouteName() === 'portal.profil.show' || Route::currentRouteName() === 'portal.profil.edit' ? 'bg-sidebar-active text-text-heading' : 'text-text-body hover:bg-sidebar-active' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6.75a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5a7.5 7.5 0 0115 0" />
                    </svg>
                    Profil
                </a>
            </nav>
        </aside>

        <div class="flex-1 flex flex-col">
            <header class="sticky top-0 z-30 border-b border-border-subtle bg-white px-6 py-4">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-2xl font-semibold text-text-heading">@yield('header', 'Dashboard')</h2>
                        <p class="text-sm text-text-label">Sistem Informasi Pencatatan Nilai Pribadi Mahasiswa (Nilaiku)</p>
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <div class="inline-flex items-center gap-3 rounded-app-input border border-border-subtle bg-white px-4 py-2 text-sm text-text-body">
                            @if (auth()->user()->profil?->foto_profil)
                                <img src="{{ asset('storage/' . auth()->user()->profil->foto_profil) }}" alt="Avatar {{ auth()->user()->name }}" class="h-10 w-10 rounded-full object-cover border border-border-subtle" />
                            @else
                                <div class="h-10 w-10 rounded-full bg-brand-blue/10 text-brand-blue flex items-center justify-center">U</div>
                            @endif
                            <div>
                                <p class="font-medium text-text-heading">{{ auth()->user()->name }}</p>
                                <p class="text-text-label">Portal User</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="rounded-app-pill border border-brand-blue bg-white px-5 py-2.5 text-sm font-medium text-brand-blue transition hover:bg-sidebar-active">Logout</button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-auto p-6 pt-10">
                @if (session('success'))
                    <div class="mb-4 rounded-app-card border border-brand-teal/20 bg-brand-teal/10 p-4 text-text-heading shadow-app-soft">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 rounded-app-card border border-rose-200 bg-rose-50 p-4 text-rose-700 shadow-app-soft">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
