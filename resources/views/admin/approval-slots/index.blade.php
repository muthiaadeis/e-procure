{{-- resources/views/admin/approval-slots/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="font-bold text-2xl text-gray-800 leading-tight">Posisi Persetujuan</h1>
            <p class="text-sm text-gray-500 mt-1">Tentukan siapa yang memegang setiap posisi tanda tangan pada Purchase Request dan Purchase Order. Jika ada pergantian staf atau berhalangan, ubah pemegang posisi di sini.</p>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        {{-- Success alert --}}
        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition
                 class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
                <button type="button" @click="show = false" class="text-green-600 hover:text-green-800">&times;</button>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm shadow-sm">
                <p class="font-semibold">Posisi persetujuan gagal disimpan:</p>
                <ul class="list-disc list-inside mt-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="p-4 bg-indigo-50 border border-indigo-100 text-indigo-800 rounded-xl text-sm">
            Hanya pengguna yang dicentang pada posisi tersebut yang dapat menandatangani dokumen. Posisi tanpa pengguna yang dipilih tidak dapat ditandatangani oleh siapa pun, sehingga proses PR dan PO akan tertahan sampai Anda menetapkan seseorang.
        </div>

        @if ($users->isEmpty())
            <div class="bg-white shadow-sm rounded-xl p-8 text-center text-gray-500 text-sm">
                Belum ada akun pengguna. Buat akun pengguna terlebih dahulu.
            </div>
        @else
            <form method="POST" action="{{ route('admin.approval-slots.update') }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    @foreach ($labels as $i => $label)
                        @php
                            $selected = $holders[$label] ?? [];
                            $docs = array_values($usage[$label] ?? []);
                        @endphp
                        <div class="bg-white rounded-2xl border {{ count($selected) === 0 ? 'border-amber-200' : 'border-gray-100' }} shadow-sm p-5">
                            <input type="hidden" name="slots[{{ $i }}][label]" value="{{ $label }}">

                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div>
                                    <h2 class="font-bold text-gray-800">{{ $label }}</h2>
                                    <p class="text-xs text-gray-400 mt-0.5">Digunakan pada {{ implode(' & ', $docs) }}</p>
                                </div>
                                @if (count($selected) === 0)
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-200 px-2.5 py-0.5 rounded-full shrink-0">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Belum ada petugas
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-green-700 bg-green-50 border border-green-200 px-2.5 py-0.5 rounded-full shrink-0">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                        {{ count($selected) }} petugas ditetapkan
                                    </span>
                                @endif
                            </div>

                            <div class="max-h-56 overflow-y-auto space-y-1 pr-1">
                                @foreach ($users as $user)
                                    <label class="flex items-center gap-3 px-2 py-1.5 rounded-lg hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox"
                                               name="slots[{{ $i }}][users][]"
                                               value="{{ $user->id }}"
                                               @checked(in_array($user->id, $selected))
                                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <span class="flex-1 min-w-0">
                                            <span class="block text-sm font-medium text-gray-800 truncate">{{ $user->name }}</span>
                                            <span class="block text-xs text-gray-400 font-mono truncate">{{ $user->email }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition shadow-sm">
                        Simpan Posisi
                    </button>
                </div>
            </form>
        @endif
    </div>
</x-app-layout>
