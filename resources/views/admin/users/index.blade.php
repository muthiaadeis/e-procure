<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manajemen User</h2>
    </x-slot>

    <div class="max-w-4xl mx-auto"
         x-data="{
            showPasswordModal: {{ session('generated_password') ? 'true' : 'false' }},
            password: @js(session('generated_password')),
            userName: @js(session('generated_password_for')),
            copied: false,
            copy() {
                navigator.clipboard.writeText(this.password);
                this.copied = true;
                setTimeout(() => this.copied = false, 2000);
            },

            showConfirmModal: false,
            confirmAction: '',
            confirmUserName: '',
            openConfirm(action, name) {
                this.confirmAction = action;
                this.confirmUserName = name;
                this.showConfirmModal = true;
            }
         }">

        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($users as $user)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-800">{{ $user->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $user->email }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $user->role }}</td>
                            <td class="px-6 py-4 text-sm">
                                @if ($user->must_change_password)
                                    <span class="text-xs font-medium text-amber-600">Belum ganti password</span>
                                @else
                                    <span class="text-xs font-medium text-green-600">Aktif</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button type="button"
                                        @click="openConfirm('{{ route('admin.users.reset-password', $user) }}', '{{ $user->name }}')"
                                        class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                                    Reset Password
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Modal: konfirmasi reset password --}}
        <div x-show="showConfirmModal" x-cloak
             x-transition:enter="ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 px-4">
            <div class="bg-white rounded-xl shadow-xl max-w-sm w-full p-6" @click.outside="showConfirmModal = false">
                <div class="flex items-center justify-center w-12 h-12 rounded-full bg-amber-50 mx-auto">
                    <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m0 3.75h.008v.008H12v-.008zM21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>

                <h3 class="text-lg font-semibold text-gray-800 text-center mt-4">Reset Password?</h3>
                <p class="text-sm text-gray-500 text-center mt-1">
                    Password lama untuk <span class="font-medium text-gray-700" x-text="confirmUserName"></span>
                    akan tidak berlaku lagi dan diganti dengan password baru.
                </p>

                <div class="mt-6 flex gap-3">
                    <button @click="showConfirmModal = false" type="button"
                            class="flex-1 py-2.5 text-sm font-medium rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">
                        Batal
                    </button>

                    <form :action="confirmAction" method="POST" class="flex-1">
                        @csrf
                        <button type="submit"
                                class="w-full py-2.5 text-sm font-medium rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">
                            Ya, Reset
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal: password hasil generate, hanya tampil sekali --}}
        <div x-show="showPasswordModal" x-cloak
             x-transition:enter="ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 px-4">
            <div class="bg-white rounded-xl shadow-xl max-w-sm w-full p-6" @click.outside="showPasswordModal = false">
                <div class="flex items-center justify-center w-12 h-12 rounded-full bg-green-50 mx-auto">
                    <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>

                <h3 class="text-lg font-semibold text-gray-800 text-center mt-4">Password Baru Dibuat</h3>
                <p class="text-sm text-gray-500 text-center mt-1">
                    Untuk user: <span class="font-medium text-gray-700" x-text="userName"></span>
                </p>

                <div class="mt-4 flex items-center gap-2">
                    <input type="text" readonly x-model="password"
                           class="flex-1 font-mono text-sm rounded-lg border-gray-300 bg-gray-50">
                    <button @click="copy()" type="button"
                            class="px-3 py-2 text-sm font-medium rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 shrink-0">
                        <span x-show="!copied">Copy</span>
                        <span x-show="copied" x-cloak>Tersalin!</span>
                    </button>
                </div>

                <p class="text-xs text-gray-400 mt-3 text-center">
                    Salin &amp; kirim password ini ke user sekarang juga. Setelah modal ini ditutup,
                    password tidak akan ditampilkan lagi (harus klik reset ulang kalau lupa).
                </p>

                <button @click="showPasswordModal = false" type="button"
                        class="mt-5 w-full text-center text-sm font-medium text-gray-500 hover:text-gray-700">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</x-app-layout>
