<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-xl font-semibold text-gray-800">Ganti Password Wajib</h2>
        <p class="mt-1 text-sm text-gray-500">
            Password Anda baru saja direset oleh admin. Buat password baru sebelum melanjutkan.
        </p>
    </div>

    <form method="POST" action="{{ route('password.force-change.update') }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <x-input-label for="current_password" value="Password Lama (dari admin)" />
            <x-text-input id="current_password" name="current_password" type="password"
                          class="block w-full mt-1 rounded-lg" required autofocus />
            <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="Password Baru" />
            <x-text-input id="password" name="password" type="password"
                          class="block w-full mt-1 rounded-lg" required />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Konfirmasi Password Baru" />
            <x-text-input id="password_confirmation" name="password_confirmation" type="password"
                          class="block w-full mt-1 rounded-lg" required />
        </div>

        <x-primary-button class="w-full justify-center py-2.5">
            Simpan Password Baru
        </x-primary-button>
    </form>
</x-guest-layout>
