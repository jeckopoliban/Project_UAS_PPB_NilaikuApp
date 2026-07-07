<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full bg-gray-100 text-gray-600" :value="old('email', $user->email)" required autocomplete="username" readonly />
            <p class="mt-2 text-sm text-gray-500">Email adalah kredensial akun dan tidak dapat diubah.</p>
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="nim_nis" :value="__('NIM / NIS')" />
            <x-text-input id="nim_nis" name="nim_nis" type="text" class="mt-1 block w-full" :value="old('nim_nis', $user->profil?->nim_nis)" autocomplete="off" />
            <x-input-error class="mt-2" :messages="$errors->get('nim_nis')" />
        </div>

        <div>
            <x-input-label for="no_hp" :value="__('No. HP')" />
            <x-text-input id="no_hp" name="no_hp" type="text" class="mt-1 block w-full" :value="old('no_hp', $user->profil?->no_hp)" autocomplete="tel" />
            <x-input-error class="mt-2" :messages="$errors->get('no_hp')" />
        </div>

        <div>
            <x-input-label for="nama_institusi" :value="__('Nama Institusi')" />
            <x-text-input id="nama_institusi" name="nama_institusi" type="text" class="mt-1 block w-full" :value="old('nama_institusi', $user->profil?->nama_institusi)" required autocomplete="organization" />
            <x-input-error class="mt-2" :messages="$errors->get('nama_institusi')" />
        </div>

        <div>
            <x-input-label for="jenis_institusi" :value="__('Jenis Institusi')" />
            <select id="jenis_institusi" name="jenis_institusi" class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="perguruan_tinggi" {{ old('jenis_institusi', $user->profil?->jenis_institusi) === 'perguruan_tinggi' ? 'selected' : '' }}>{{ __('Perguruan Tinggi') }}</option>
                <option value="sekolah" {{ old('jenis_institusi', $user->profil?->jenis_institusi) === 'sekolah' ? 'selected' : '' }}>{{ __('Sekolah') }}</option>
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('jenis_institusi')" />
        </div>

        <div>
            <x-input-label for="program_studi" :value="__('Program Studi')" />
            <x-text-input id="program_studi" name="program_studi" type="text" class="mt-1 block w-full" :value="old('program_studi', $user->profil?->program_studi)" />
            <x-input-error class="mt-2" :messages="$errors->get('program_studi')" />
        </div>

        <div>
            <x-input-label for="target_ipk" :value="__('Target IPK')" />
            <x-text-input id="target_ipk" name="target_ipk" type="number" step="0.01" min="0" max="4.00" class="mt-1 block w-full" :value="old('target_ipk', $user->profil?->target_ipk)" />
            <x-input-error class="mt-2" :messages="$errors->get('target_ipk')" />
        </div>

        <div>
            <x-input-label for="foto_profil" :value="__('Foto Profil')" />
            <input id="foto_profil" name="foto_profil" type="file" class="mt-1 block w-full text-sm text-gray-500 file:border-0 file:bg-emerald-600 file:px-4 file:py-2 file:text-white file:rounded-lg" />
            <p class="mt-2 text-sm text-gray-500">Maksimum ukuran file: 2 MB. Format gambar apa pun yang valid diterima.</p>
            @if ($user->profil?->foto_profil)
                <p class="mt-2 text-sm text-gray-600">{{ __('Foto saat ini:') }} <span class="font-semibold">{{ basename($user->profil->foto_profil) }}</span></p>
            @endif
            <x-input-error class="mt-2" :messages="$errors->get('foto_profil')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
