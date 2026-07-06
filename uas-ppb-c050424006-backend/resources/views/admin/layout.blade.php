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
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 rounded-app-btn px-4 py-3 text-sm font-medium transition {{ Route::currentRouteName() === 'admin.dashboard' ? 'bg-sidebar-active text-text-heading shadow-app-soft' : 'text-text-body hover:bg-sidebar-active' }}">
                    Dashboard
                </a>
                <a href="{{ route('admin.user-management.index') }}" class="flex items-center gap-3 rounded-app-btn px-4 py-3 text-sm font-medium transition {{ str_starts_with(Route::currentRouteName(), 'admin.user-management') ? 'bg-sidebar-active text-text-heading shadow-app-soft' : 'text-text-body hover:bg-sidebar-active' }}">
                    User Management
                </a>
                <a href="{{ route('admin.grading-template.index') }}" class="flex items-center gap-3 rounded-app-btn px-4 py-3 text-sm font-medium transition {{ str_starts_with(Route::currentRouteName(), 'admin.grading-template') ? 'bg-sidebar-active text-text-heading shadow-app-soft' : 'text-text-body hover:bg-sidebar-active' }}">
                    Grading Template
                </a>
                <a href="{{ route('admin.institusi-referensi.index') }}" class="flex items-center gap-3 rounded-app-btn px-4 py-3 text-sm font-medium transition {{ str_starts_with(Route::currentRouteName(), 'admin.institusi-referensi') ? 'bg-sidebar-active text-text-heading shadow-app-soft' : 'text-text-body hover:bg-sidebar-active' }}">
                    Institusi Referensi
                </a>
                <a href="{{ route('admin.system.audit-logs') }}" class="flex items-center gap-3 rounded-app-btn px-4 py-3 text-sm font-medium transition {{ str_starts_with(Route::currentRouteName(), 'admin.system.audit-logs') ? 'bg-sidebar-active text-text-heading shadow-app-soft' : 'text-text-body hover:bg-sidebar-active' }}">
                    Audit Logs
                </a>
                <a href="{{ route('admin.system.error-logs') }}" class="flex items-center gap-3 rounded-app-btn px-4 py-3 text-sm font-medium transition {{ str_starts_with(Route::currentRouteName(), 'admin.system.error-logs') ? 'bg-sidebar-active text-text-heading shadow-app-soft' : 'text-text-body hover:bg-sidebar-active' }}">
                    Error Logs
                </a>
            </nav>
        </aside>

        <div class="flex-1 flex flex-col">
            <header class="sticky top-0 z-30 border-b border-border-subtle bg-white px-6 py-4">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-2xl font-semibold text-text-heading">@yield('header', 'Dashboard')</h2>
                        <p class="text-sm text-text-label">@yield('subheader', 'Panel administrasi sistem Nilaiku.')</p>
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <div class="inline-flex items-center gap-3 rounded-app-input border border-border-subtle bg-white px-4 py-2 text-sm text-text-body">
                            <div class="h-10 w-10 rounded-full bg-brand-blue/10 text-brand-blue flex items-center justify-center">A</div>
                            <div>
                                <p class="font-medium text-text-heading">{{ auth()->user()->name }}</p>
                                <p class="text-text-label">Super Administrator</p>
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
