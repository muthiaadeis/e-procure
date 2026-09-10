<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manajemen User</h2>
    </x-slot>

    <div class="max-w-4xl mx-auto"
         x-data="{
            showModal: {{ session('generated_password') ? 'true' : 'false' }},
            password: @js(session('generated_password')),
            userName: @js(session('generated_password_for')),
            copied: false,
            copy() {
                navigator.clipboard.writeText(this.password);
                this.copied = true;
                setTimeout(() => this.copied = false, 2000);
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
                                <form method="POST" action="{{ route('admin.users.reset-password', $user) }}"
                                      onsubmit="return confirm('Reset password untuk {{ $user->name }}? Password lama akan tidak berlaku lagi.');">
                                    @csrf
                                    <button type="submit" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                                        Reset Password
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Modal: password hasil generate, hanya tampil sekali --}}
        <div x-show="showModal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 px-4">
            <div class="bg-white rounded-xl shadow-xl max-w-sm w-full p-6" @click.outside="showModal = false">
                <h3 class="text-lg font-semibold text-gray-800">Password Baru Dibuat</h3>
                <p class="text-sm text-gray-500 mt-1">
                    Untuk user: <span class="font-medium" x-text="userName"></span>
                </p>

                <div class="mt-4 flex items-center gap-2">
                    <input type="text" readonly x-model="password"
                           class="flex-1 font-mono text-sm rounded-lg border-gray-300 bg-gray-50">
                    <button @click="copy()" type="button"
                            class="px-3 py-2 text-sm font-medium rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">
                        <span x-show="!copied">Copy</span>
                        <span x-show="copied" x-cloak>Tersalin!</span>
                    </button>
                </div>

                <p class="text-xs text-gray-400 mt-3">
                    Salin &amp; kirim password ini ke user sekarang juga. Setelah modal ini ditutup,
                    password tidak akan ditampilkan lagi (harus klik reset ulang kalau lupa).
                </p>

                <button @click="showModal = false" type="button"
                        class="mt-5 w-full text-center text-sm font-medium text-gray-500 hover:text-gray-700">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</x-app-layout>
