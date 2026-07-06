<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gray-100 px-4 py-10">
        <div class="w-full max-w-2xl overflow-hidden rounded-app-card bg-white shadow-app-card">
            <div class="grid grid-cols-1 md:grid-cols-2">
                <div class="order-2 md:order-1 flex items-center justify-center bg-white px-6 py-10 md:px-8">
                    <div class="w-full max-w-lg">
                        <div class="mb-8">
                            <h1 class="text-3xl font-bold text-text-heading">Masuk ke Akun Anda</h1>
                            <p class="mt-3 text-sm text-text-muted">Masuk dengan email dan password terdaftar Anda.</p>
                        </div>

                        @if ($errors->any())
                            <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                                <ul class="list-disc space-y-1 pl-5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}" class="space-y-6">
                            @csrf

                            <div>
                                <label for="email" class="mb-2 block text-sm font-medium text-text-heading">Email</label>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-text-heading outline-none transition focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20" />
                                @error('email')
                                    <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password" class="mb-2 block text-sm font-medium text-text-heading">Password</label>
                                <div class="relative">
                                    <input id="password" type="password" name="password" required autocomplete="current-password" class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 pr-12 text-sm text-text-heading outline-none transition focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20" />
                                    <button type="button" onclick="togglePasswordVisibility('password', 'eyeIcon1')" class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 transition hover:text-brand-blue">
                                        <svg id="eyeIcon1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                    </button>
                                </div>
                                @error('password')
                                    <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between text-sm">
                                <label for="remember_me" class="inline-flex items-center gap-2 text-text-body">
                                    <input id="remember_me" name="remember" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-brand-blue focus:ring-brand-blue" {{ old('remember') ? 'checked' : '' }}>
                                    Ingat saya
                                </label>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="text-brand-blue font-medium transition hover:text-brand-blue/80">Lupa password?</a>
                                @endif
                            </div>

                            <button type="submit" class="w-full rounded-app-pill bg-gradient-button px-4 py-3 text-sm font-semibold text-white shadow-app-card transition hover:shadow-xl">Masuk</button>
                        </form>
                    </div>
                </div>

                <div class="order-1 md:order-2 flex items-center justify-center bg-gradient-button px-6 py-10 md:px-8 text-white">
                    <div class="w-full max-w-md text-center">
                        <h2 class="text-3xl font-semibold">Halo, Teman!</h2>
                        <p class="mt-4 text-sm leading-relaxed text-white/90">Daftarkan diri Anda dan mulai gunakan layanan kami segera.</p>
                        <a href="{{ route('register') }}" class="mt-8 inline-flex rounded-app-pill border border-white px-6 py-3 text-sm font-semibold text-white transition hover:bg-white hover:text-brand-blue">Daftar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePasswordVisibility(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (!input || !icon) return;

            const eyeSvg = `
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                <circle cx="12" cy="12" r="3" />
            `;
            const eyeOffSvg = `
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                <path d="M6 6l12 12" />
                <circle cx="12" cy="12" r="3" />
            `;

            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = eyeOffSvg;
            } else {
                input.type = 'password';
                icon.innerHTML = eyeSvg;
            }
        }
    </script>
</x-guest-layout>
