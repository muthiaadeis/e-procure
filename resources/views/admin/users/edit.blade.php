<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="font-bold text-2xl text-gray-800 leading-tight">Edit Akun Pengguna</h1>
                <p class="text-sm text-gray-500 mt-1">Perbarui informasi profil dan peran sistem untuk {{ $user->name }}.</p>
            </div>
            <a href="{{ route('admin.users.index') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-50 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Daftar Pengguna
            </a>
        </div>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">

        @if (($errors ?? null)?->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                <div class="flex items-center gap-2 text-red-700 font-semibold text-sm mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Silakan perbaiki kesalahan berikut:</span>
                </div>
                <ul class="list-disc list-inside text-xs text-red-600 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="bg-white shadow-sm rounded-2xl border border-gray-100 p-6 sm:p-8 space-y-8">
            @csrf
            @method('PUT')

            {{-- 1. Identity --}}
            <div>
                <h3 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-3 mb-5 flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs font-bold">1</span>
                    Identitas Pengguna & Kontak
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="name"
                               required
                               value="{{ old('name', $user->name) }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Alamat Email (Username Login) <span class="text-red-500">*</span>
                        </label>
                        <input type="email"
                               name="email"
                               required
                               value="{{ old('email', $user->email) }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>
                </div>
            </div>

            {{-- 2. Role --}}
            <div>
                <h3 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-3 mb-5 flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs font-bold">2</span>
                    Penetapan Peran & Alur Kerja
                </h3>

                @php $currentRole = old('role', $user->role); @endphp
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Role: input --}}
                    <label class="relative flex flex-col p-4 border rounded-xl cursor-pointer hover:bg-gray-50/80 transition {{ $currentRole === 'input' ? 'border-indigo-500 ring-2 ring-indigo-100 bg-indigo-50/20' : 'border-gray-200' }}">
                        <div class="flex items-center justify-between mb-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                                Staff / Requester
                            </span>
                            <input type="radio" name="role" value="input" class="text-indigo-600 focus:ring-indigo-500"
                                   {{ $currentRole === 'input' ? 'checked' : '' }}>
                        </div>
                        <p class="text-sm font-bold text-gray-800">User Input</p>
                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                            Dapat membuat dan mengajukan draft Material Request (MR) dan Pembelian Lokal (RRP).
                        </p>
                    </label>

                    {{-- Role: approver_a --}}
                    <label class="relative flex flex-col p-4 border rounded-xl cursor-pointer hover:bg-gray-50/80 transition {{ $currentRole === 'approver_a' ? 'border-indigo-500 ring-2 ring-indigo-100 bg-indigo-50/20' : 'border-gray-200' }}">
                        <div class="flex items-center justify-between mb-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-100">
                                Approver A
                            </span>
                            <input type="radio" name="role" value="approver_a" class="text-indigo-600 focus:ring-indigo-500"
                                   {{ $currentRole === 'approver_a' ? 'checked' : '' }}>
                        </div>
                        <p class="text-sm font-bold text-gray-800">Penyetuju Tahap 1</p>
                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                            Meninjau dan menyetujui pengajuan Material Request (Persetujuan Tahap 1).
                        </p>
                    </label>

                    {{-- Role: approver_c --}}
                    <label class="relative flex flex-col p-4 border rounded-xl cursor-pointer hover:bg-gray-50/80 transition {{ $currentRole === 'approver_c' ? 'border-indigo-500 ring-2 ring-indigo-100 bg-indigo-50/20' : 'border-gray-200' }}">
                        <div class="flex items-center justify-between mb-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-100">
                                Approver C
                            </span>
                            <input type="radio" name="role" value="approver_c" class="text-indigo-600 focus:ring-indigo-500"
                                   {{ $currentRole === 'approver_c' ? 'checked' : '' }}>
                        </div>
                        <p class="text-sm font-bold text-gray-800">Penyetuju Tahap 2</p>
                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                            Pengambil keputusan akhir yang menyetujui Material Request sebelum pencairan Finance (Persetujuan Tahap 2).
                        </p>
                    </label>

                    {{-- Role: finance --}}
                    <label class="relative flex flex-col p-4 border rounded-xl cursor-pointer hover:bg-gray-50/80 transition {{ $currentRole === 'finance' ? 'border-indigo-500 ring-2 ring-indigo-100 bg-indigo-50/20' : 'border-gray-200' }}">
                        <div class="flex items-center justify-between mb-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                Finance / Cashier
                            </span>
                            <input type="radio" name="role" value="finance" class="text-indigo-600 focus:ring-indigo-500"
                                   {{ $currentRole === 'finance' ? 'checked' : '' }}>
                        </div>
                        <p class="text-sm font-bold text-gray-800">Keuangan & Pembayaran</p>
                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                            Memverifikasi pembayaran dan menandai Material Request yang disetujui sebagai Lunas.
                        </p>
                    </label>
                </div>

                {{-- Admin Access Checkbox --}}
                <div class="mt-4 p-4 rounded-xl bg-gray-50 border border-gray-200/80 flex items-start gap-3">
                    <input type="checkbox"
                           name="is_admin"
                           id="is_admin"
                           value="1"
                           {{ old('is_admin', $user->is_admin) ? 'checked' : '' }}
                           {{ $user->id === auth()->id() ? 'disabled' : '' }}
                           class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="is_admin" class="cursor-pointer select-none">
                        <span class="block text-sm font-bold text-gray-800">Akses Administrator</span>
                        <span class="block text-xs text-gray-500 mt-0.5">
                            @if ($user->id === auth()->id())
                                (Anda tidak dapat mencabut hak admin dari akun Anda yang sedang login).
                            @else
                                Mengizinkan pengguna ini mengakses Panel Admin, mengelola akun pengguna, dan mereset password.
                            @endif
                        </span>
                    </label>
                </div>

                {{-- Force password change checkbox --}}
                <div class="mt-3 p-4 rounded-xl bg-gray-50 border border-gray-200/80 flex items-start gap-3">
                    <input type="checkbox"
                           name="must_change_password"
                           id="must_change_password"
                           value="1"
                           {{ old('must_change_password', $user->must_change_password) ? 'checked' : '' }}
                           class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="must_change_password" class="cursor-pointer select-none">
                        <span class="block text-sm font-bold text-gray-800">Wajibkan Ganti Password pada Login Berikutnya</span>
                        <span class="block text-xs text-gray-500 mt-0.5">
                            Jika diaktifkan, pengguna akan diminta memilih password baru saat login berikutnya.
                        </span>
                    </label>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.users.index') }}"
                   class="px-4 py-2.5 rounded-lg border border-gray-200 text-gray-600 font-semibold text-sm hover:bg-gray-50 transition">
                    Batal
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-indigo-600 text-white font-semibold text-sm hover:bg-indigo-700 shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
