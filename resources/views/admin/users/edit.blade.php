<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="font-bold text-2xl text-gray-800 leading-tight">Edit User Account</h1>
                <p class="text-sm text-gray-500 mt-1">Update profile information and system role for {{ $user->name }}.</p>
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

    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">

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

        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="bg-white shadow-sm rounded-2xl border border-gray-100 p-6 sm:p-8 space-y-8">
            @csrf
            @method('PUT')

            {{-- 1. Identity --}}
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
                               value="{{ old('name', $user->name) }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Email Address (Login Username) <span class="text-red-500">*</span>
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
                    Role & Workflow Assignment
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
                            Can draft and submit Material Requests (MR) and initiate Local Purchase (RRP) documents.
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
                        <p class="text-sm font-bold text-gray-800">First-Stage Approver</p>
                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                            Reviews and authorizes newly submitted Material Requests (Approval Stage 1).
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
                        <p class="text-sm font-bold text-gray-800">Second-Stage Approver</p>
                        <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                            Final decision maker approving Material Requests before Finance disbursement (Approval Stage 2).
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
                           {{ old('is_admin', $user->is_admin) ? 'checked' : '' }}
                           {{ $user->id === auth()->id() ? 'disabled' : '' }}
                           class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="is_admin" class="cursor-pointer select-none">
                        <span class="block text-sm font-bold text-gray-800">Administrator Access</span>
                        <span class="block text-xs text-gray-500 mt-0.5">
                            @if ($user->id === auth()->id())
                                (You cannot remove admin rights from your currently logged-in account).
                            @else
                                Allows this user to access the Admin Panel, manage user accounts, and reset passwords.
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
                        <span class="block text-sm font-bold text-gray-800">Require Password Change on Next Login</span>
                        <span class="block text-xs text-gray-500 mt-0.5">
                            When enabled, the user will be prompted to choose a new personal password upon their next login.
                        </span>
                    </label>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
