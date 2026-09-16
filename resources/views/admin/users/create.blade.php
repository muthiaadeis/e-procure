<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="font-bold text-2xl text-gray-800 leading-tight">Create User Account</h1>
                <p class="text-sm text-gray-500 mt-1">Generate new login credentials without having to use command-line tinker.</p>
            </div>
            <a href="{{ route('admin.users.index') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-50 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to User List
            </a>
        </div>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8"
         x-data="{
            passwordType: 'auto',
            autoPassword: @js($suggestedPassword),
            showManualPassword: false,
            generateNewPassword() {
                const upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
                const lower = 'abcdefghijkmnpqrstuvwxyz';
                const nums = '23456789';
                const syms = '!@#$%^&*?';
                const all = upper + lower + nums + syms;
                let pwd = [
                    upper[Math.floor(Math.random() * upper.length)],
                    lower[Math.floor(Math.random() * lower.length)],
                    nums[Math.floor(Math.random() * nums.length)],
                    syms[Math.floor(Math.random() * syms.length)]
                ];
                for (let i = 4; i < 12; i++) {
                    pwd.push(all[Math.floor(Math.random() * all.length)]);
                }
                this.autoPassword = pwd.sort(() => 0.5 - Math.random()).join('');
            }
         }">

        @if (($errors ?? null)?->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                <div class="flex items-center gap-2 text-red-700 font-semibold text-sm mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Please correct the errors below:</span>
                </div>
                <ul class="list-disc list-inside text-xs text-red-600 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.users.store') }}" class="bg-white shadow-sm rounded-2xl border border-gray-100 p-6 sm:p-8 space-y-8">
            @csrf

            {{-- 1. Account Details --}}
            <div>
                <h3 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-3 mb-5 flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs font-bold">1</span>
                    User Identity & Contact
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="name"
                               required
                               value="{{ old('name') }}"
                               placeholder="e.g. Suparlan or Ulfa Gustianti"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Email Address (Login Username) <span class="text-red-500">*</span>
                        </label>
                        <input type="email"
                               name="email"
                               required
                               value="{{ old('email') }}"
                               placeholder="e.g. user@prkkemari.com"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>
                </div>
            </div>

            {{-- 2. Role & System Permissions --}}
            <div>
                <h3 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-3 mb-5 flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs font-bold">2</span>
                    Role & Workflow Assignment
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Role: input --}}
                    <label class="relative flex flex-col p-4 border rounded-xl cursor-pointer hover:bg-gray-50/80 transition"
                           :class="'{{ old('role', 'input') }}' === 'input' ? 'border-indigo-500 ring-2 ring-indigo-100 bg-indigo-50/20' : 'border-gray-200'">
                        <div class="flex items-center justify-between mb-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                                Staff / Requester
                            </span>
                            <input type="radio" name="role" value="input" class="text-indigo-600 focus:ring-indigo-500"
                                   {{ old('role', 'input') === 'input' ? 'checked' : '' }}>
                        </div>
                        <p class="text-sm font-bold text-gray-800">User Input</p>
                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                            Can draft and submit Material Requests (MR) and initiate Local Purchase (RRP) documents.
                        </p>
                    </label>

                    {{-- Role: approver_a --}}
                    <label class="relative flex flex-col p-4 border rounded-xl cursor-pointer hover:bg-gray-50/80 transition"
                           :class="'{{ old('role') }}' === 'approver_a' ? 'border-indigo-500 ring-2 ring-indigo-100 bg-indigo-50/20' : 'border-gray-200'">
                        <div class="flex items-center justify-between mb-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-100">
                                Approver A
                            </span>
                            <input type="radio" name="role" value="approver_a" class="text-indigo-600 focus:ring-indigo-500"
                                   {{ old('role') === 'approver_a' ? 'checked' : '' }}>
                        </div>
                        <p class="text-sm font-bold text-gray-800">First-Stage Approver</p>
                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                            Reviews and authorizes newly submitted Material Requests (Approval Stage 1).
                        </p>
                    </label>

                    {{-- Role: approver_c --}}
                    <label class="relative flex flex-col p-4 border rounded-xl cursor-pointer hover:bg-gray-50/80 transition"
                           :class="'{{ old('role') }}' === 'approver_c' ? 'border-indigo-500 ring-2 ring-indigo-100 bg-indigo-50/20' : 'border-gray-200'">
                        <div class="flex items-center justify-between mb-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-100">
                                Approver C
                            </span>
                            <input type="radio" name="role" value="approver_c" class="text-indigo-600 focus:ring-indigo-500"
                                   {{ old('role') === 'approver_c' ? 'checked' : '' }}>
                        </div>
                        <p class="text-sm font-bold text-gray-800">Second-Stage Approver</p>
                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                            Final decision maker approving Material Requests before Finance disbursement (Approval Stage 2).
                        </p>
                    </label>

                    {{-- Role: finance --}}
                    <label class="relative flex flex-col p-4 border rounded-xl cursor-pointer hover:bg-gray-50/80 transition"
                           :class="'{{ old('role') }}' === 'finance' ? 'border-indigo-500 ring-2 ring-indigo-100 bg-indigo-50/20' : 'border-gray-200'">
                        <div class="flex items-center justify-between mb-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                Finance / Cashier
                            </span>
                            <input type="radio" name="role" value="finance" class="text-indigo-600 focus:ring-indigo-500"
                                   {{ old('role') === 'finance' ? 'checked' : '' }}>
                        </div>
                        <p class="text-sm font-bold text-gray-800">Finance & Payment</p>
                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                            Verifies payment execution and marks approved Material Requests as Paid.
                        </p>
                    </label>
                </div>

                {{-- Admin Access Checkbox --}}
                <div class="mt-4 p-4 rounded-xl bg-gray-50 border border-gray-200/80 flex items-start gap-3">
                    <input type="checkbox"
                           name="is_admin"
                           id="is_admin"
                           value="1"
                           {{ old('is_admin') ? 'checked' : '' }}
                           class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="is_admin" class="cursor-pointer select-none">
                        <span class="block text-sm font-bold text-gray-800">Grant Administrator Access</span>
                        <span class="block text-xs text-gray-500 mt-0.5">
                            Allows this user to access the Admin Panel, manage user accounts, and reset passwords.
                        </span>
                    </label>
                </div>
            </div>

            {{-- 3. Password Setup --}}
            <div>
                <h3 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-3 mb-5 flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs font-bold">3</span>
                    Password & Security
                </h3>

                <div class="space-y-4">
                    <div class="flex items-center gap-6">
                        <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700 cursor-pointer">
                            <input type="radio" name="password_type" value="auto" x-model="passwordType" class="text-indigo-600 focus:ring-indigo-500">
                            Auto-generate strong password (Recommended)
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700 cursor-pointer">
                            <input type="radio" name="password_type" value="manual" x-model="passwordType" class="text-indigo-600 focus:ring-indigo-500">
                            Set manual password
                        </label>
                    </div>

                    {{-- Auto Password Box --}}
                    <div x-show="passwordType === 'auto'" class="p-4 rounded-xl bg-indigo-50/50 border border-indigo-100">
                        <p class="text-xs font-semibold text-indigo-900 uppercase tracking-wider mb-2">Generated Password Preview</p>
                        <div class="flex items-center gap-3">
                            <input type="text"
                                   readonly
                                   :value="autoPassword"
                                   class="font-mono text-sm bg-white border border-indigo-200 rounded-lg px-3 py-2 text-gray-800 flex-1 select-all">
                            <button type="button"
                                    @click="generateNewPassword()"
                                    class="px-3 py-2 text-xs font-semibold text-indigo-600 bg-white border border-indigo-200 rounded-lg hover:bg-indigo-50 transition shrink-0 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Regenerate
                            </button>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-2">
                            A pop-up modal will appear after submitting with complete copy-to-clipboard login credentials to give to the user.
                        </p>
                    </div>

                    {{-- Manual Password Input --}}
                    <div x-show="passwordType === 'manual'" x-cloak class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Initial Password <span class="text-red-500">*</span></label>
                        <div class="relative max-w-md">
                            <input :type="showManualPassword ? 'text' : 'password'"
                                   name="password"
                                   minlength="6"
                                   placeholder="Enter minimum 6 characters..."
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm pr-10">
                            <button type="button"
                                    @click="showManualPassword = !showManualPassword"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                <svg x-show="!showManualPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="showManualPassword" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Force password change checkbox --}}
                    <div class="pt-2 flex items-center gap-2.5">
                        <input type="checkbox"
                               name="must_change_password"
                               id="must_change_password"
                               value="1"
                               checked
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="must_change_password" class="text-xs font-medium text-gray-700 cursor-pointer select-none">
                            Require user to change their password upon their first login (Recommended)
                        </label>
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.users.index') }}"
                   class="px-4 py-2.5 rounded-lg border border-gray-200 text-gray-600 font-semibold text-sm hover:bg-gray-50 transition">
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-indigo-600 text-white font-semibold text-sm hover:bg-indigo-700 shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    Create User & Generate Login
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
