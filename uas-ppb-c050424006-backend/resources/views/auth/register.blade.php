<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gray-100 px-4 py-10">
        <div class="w-full max-w-2xl overflow-hidden rounded-app-card bg-white shadow-app-card">
            <div class="grid grid-cols-1 md:grid-cols-2">
                <div class="order-2 md:order-1 flex h-full items-center justify-center bg-white px-6 py-10 md:px-8">
                    <div class="w-full max-w-lg">
                        <div class="mb-8">
                            <h1 class="text-3xl font-bold text-text-heading">Daftar Akun Baru</h1>
                            <p class="mt-3 text-sm text-text-muted">Buat akun untuk mulai menggunakan layanan Nilaiku.</p>
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

                        <form method="POST" action="{{ route('register') }}" class="space-y-6">
                            @csrf

                            <div>
                                <label for="name" class="mb-2 block text-sm font-medium text-text-heading">Nama Lengkap</label>
                                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-text-heading outline-none transition focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20" />
                                @error('name')
                                    <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email" class="mb-2 block text-sm font-medium text-text-heading">Email</label>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-text-heading outline-none transition focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20" />
                                @error('email')
                                    <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="nama_institusi" class="mb-2 block text-sm font-medium text-text-heading">Nama Institusi</label>
                                <input id="nama_institusi" type="text" name="nama_institusi" value="{{ old('nama_institusi') }}" required autocomplete="organization" class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-text-heading outline-none transition focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20" />
                                @error('nama_institusi')
                                    <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="jenis_institusi" class="mb-2 block text-sm font-medium text-text-heading">Jenis Institusi</label>
                                <select id="jenis_institusi" name="jenis_institusi" required class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-text-heading outline-none transition focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20">
                                    <option value="perguruan_tinggi" {{ old('jenis_institusi') === 'perguruan_tinggi' ? 'selected' : '' }}>Perguruan Tinggi</option>
                                    <option value="sekolah" {{ old('jenis_institusi') === 'sekolah' ? 'selected' : '' }}>Sekolah</option>
                                </select>
                                @error('jenis_institusi')
                                    <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password" class="mb-2 block text-sm font-medium text-text-heading">Password</label>
                                <div class="relative">
                                    <input id="password" type="password" name="password" required autocomplete="new-password" class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 pr-12 text-sm text-text-heading outline-none transition focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20" />
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

                            <div>
                                <label for="password_confirmation" class="mb-2 block text-sm font-medium text-text-heading">Konfirmasi Password</label>
                                <div class="relative">
                                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 pr-12 text-sm text-text-heading outline-none transition focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20" />
                                    <button type="button" onclick="togglePasswordVisibility('password_confirmation', 'eyeIcon2')" class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 transition hover:text-brand-blue">
                                        <svg id="eyeIcon2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                    </button>
                                </div>
                                @error('password_confirmation')
                                    <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <button type="submit" class="w-full rounded-app-pill bg-gradient-button px-4 py-3 text-sm font-semibold text-white shadow-app-card transition hover:shadow-xl">Daftar</button>
                        </form>
                    </div>
                </div>

                <div class="order-1 md:order-2 flex items-center justify-center bg-gradient-button px-6 py-10 md:px-8 text-white">
                    <div class="w-full max-w-md text-center">
                        <h2 class="text-3xl font-semibold">Selamat Datang Kembali!</h2>
                        <p class="mt-4 text-sm leading-relaxed text-white/90">Untuk tetap terhubung dengan kami, silakan masuk dengan akun Anda.</p>
                        <a href="{{ route('login') }}" class="mt-8 inline-flex rounded-app-pill border border-white px-6 py-3 text-sm font-semibold text-white transition hover:bg-white hover:text-brand-blue">Masuk</a>
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
                <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.86 21.86 0 0 1 5.46-6.62" />
                <path d="M3 3l18 18" />
                <path d="M9.53 9.53a3.5 3.5 0 0 0 4.95 4.95" />
                <path d="M14.12 14.12a3.5 3.5 0 0 1-4.95-4.95" />
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
