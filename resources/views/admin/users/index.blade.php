<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manajemen User</h2>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="font-bold text-2xl text-gray-800 leading-tight">User Management</h1>
                <p class="text-sm text-gray-500 mt-1">Create and manage user accounts, assign workflow roles, and maintain security credentials.</p>
            </div>
            <a href="{{ route('admin.users.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                Create User
            </a>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto"
    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6"
         x-data="{
            showPasswordModal: {{ session('generated_password') ? 'true' : 'false' }},
            password: @js(session('generated_password')),
            userName: @js(session('generated_password_for')),
            copied: false,
            copy() {
            isNewUser: {{ session('is_new_user') ? 'true' : 'false' }},
            password: @js(session('generated_password') ?? ''),
            userName: @js(session('generated_password_for') ?? ''),
            userEmail: @js(session('generated_email_for') ?? ''),
            copiedPassword: false,
            copiedAll: false,

            copyPassword() {
                navigator.clipboard.writeText(this.password);
                this.copied = true;
                setTimeout(() => this.copied = false, 2000);
                this.copiedPassword = true;
                setTimeout(() => this.copiedPassword = false, 2000);
            },

            showConfirmModal: false,
            confirmAction: '',
            confirmUserName: '',
            openConfirm(action, name) {
                this.confirmAction = action;
                this.confirmUserName = name;
                this.showConfirmModal = true;
            copyAll() {
                const text = 'e-Procure Login Details\nName: ' + this.userName + '\nEmail: ' + this.userEmail + '\nPassword: ' + this.password + '\nLogin URL: ' + window.location.origin + '/login';
                navigator.clipboard.writeText(text);
                this.copiedAll = true;
                setTimeout(() => this.copiedAll = false, 2000);
            },

            showConfirmReset: false,
            resetAction: '',
            resetUserName: '',
            openReset(action, name) {
                this.resetAction = action;
                this.resetUserName = name;
                this.showConfirmReset = true;
            },

            showConfirmDelete: false,
            deleteAction: '',
            deleteUserName: '',
            openDelete(action, name) {
                this.deleteAction = action;
                this.deleteUserName = name;
                this.showConfirmDelete = true;
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

        {{-- Error alert --}}
        @if (session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)" x-transition
                 class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
                <button type="button" @click="show = false" class="text-red-600 hover:text-red-800">&times;</button>
            </div>
        @endif

        {{-- Top Summary Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Users</span>
                    <span class="w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </span>
                </div>
                <p class="text-2xl font-bold text-gray-800">{{ $totalUsers }}</p>
                <p class="text-xs text-gray-500 mt-1">Registered accounts</p>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Administrators</span>
                    <span class="w-9 h-9 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </span>
                </div>
                <p class="text-2xl font-bold text-gray-800">{{ $totalAdmins }}</p>
                <p class="text-xs text-gray-500 mt-1">Full system privilege</p>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Approvers & Staff</span>
                    <span class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </span>
                </div>
                <p class="text-2xl font-bold text-gray-800">{{ $totalApprovers }}</p>
                <p class="text-xs text-gray-500 mt-1">Approval A & C members</p>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Pending Password</span>
                    <span class="w-9 h-9 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                    </span>
                </div>
                <p class="text-2xl font-bold text-gray-800">{{ $pendingPasswordChanges }}</p>
                <p class="text-xs text-gray-500 mt-1">Must change on login</p>
            </div>
        </div>

        {{-- Filter & Search Card --}}
        <div class="bg-white shadow-sm rounded-xl p-4 sm:p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex items-center gap-2 w-full sm:w-auto flex-1 max-w-md">
                <div class="relative w-full">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
                        </svg>
                    </span>
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Search by name, email, or role..."
                           class="w-full pl-9 pr-8 py-2 text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                    @if ($search !== '')
                        <a href="{{ route('admin.users.index') }}"
                           class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </a>
                    @endif
                </div>
                <button type="submit" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition shrink-0">
                    Search
                </button>
            </form>

            <span class="text-xs text-gray-400 shrink-0">
                Showing <strong class="text-gray-700">{{ $users->count() }}</strong> user account(s)
            </span>
        </div>

        {{-- Users Table --}}
        <div class="bg-white shadow-sm rounded-2xl border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50/75">
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
                            <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">User Account</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Workflow Role</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Privilege</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Password Status</th>
                            <th class="px-6 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    @endforeach
                </tbody>
            </table>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($users as $user)
                            @php
                                $roleBadge = match($user->role) {
                                    'approver_a' => ['label' => 'Approver A', 'class' => 'bg-amber-50 text-amber-700 border-amber-200'],
                                    'approver_c' => ['label' => 'Approver C', 'class' => 'bg-purple-50 text-purple-700 border-purple-200'],
                                    'finance' => ['label' => 'Finance', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                                    default => ['label' => 'Staff / Input', 'class' => 'bg-blue-50 text-blue-700 border-blue-200'],
                                };

                                $initials = strtoupper(substr($user->name, 0, 2));
                            @endphp
                            <tr class="hover:bg-gray-50/70 transition">
                                {{-- Name & Email --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-700 font-bold text-xs flex items-center justify-center shrink-0">
                                            {{ $initials }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-800 flex items-center gap-1.5">
                                                {{ $user->name }}
                                                @if ($user->id === auth()->id())
                                                    <span class="px-1.5 py-0.2 rounded text-[10px] bg-gray-100 text-gray-600 font-normal">You</span>
                                                @endif
                                            </p>
                                            <p class="text-xs text-gray-500 font-mono">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Workflow Role --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $roleBadge['class'] }}">
                                        {{ $roleBadge['label'] }}
                                    </span>
                                </td>

                                {{-- Privilege --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($user->is_admin)
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-purple-700 bg-purple-50 border border-purple-200 px-2.5 py-0.5 rounded-full">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                            </svg>
                                            Administrator
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-500 font-medium">Standard</span>
                                    @endif
                                </td>

                                {{-- Password Status --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($user->must_change_password)
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-200 px-2.5 py-0.5 rounded-full">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Must Change Password
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-green-700 bg-green-50 border border-green-200 px-2.5 py-0.5 rounded-full">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                            Active
                                        </span>
                                    @endif
                                </td>

                                {{-- Actions --}}
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="inline-flex items-center gap-1">
                                        {{-- Edit Button --}}
                                        <a href="{{ route('admin.users.edit', $user) }}"
                                           title="Edit User"
                                           class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>

                                        {{-- Reset Password Button --}}
                                        <button type="button"
                                                title="Reset Password"
                                                @click="openReset('{{ route('admin.users.reset-password', $user) }}', '{{ $user->name }}')"
                                                class="p-1.5 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                            </svg>
                                        </button>

                                        {{-- Delete Button (Disabled for Self) --}}
                                        @if ($user->id !== auth()->id())
                                            <button type="button"
                                                    title="Delete User"
                                                    @click="openDelete('{{ route('admin.users.destroy', $user) }}', '{{ $user->name }}')"
                                                    class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                    </svg>
                                    <p class="font-semibold text-gray-600">No users found</p>
                                    <p class="text-xs text-gray-400 mt-1">Try a different search or create a new user account.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Modal: konfirmasi reset password --}}
        <div x-show="showConfirmModal" x-cloak
        {{-- Modal: Generated Password & Credentials (Displays after create or reset) --}}
        <div x-show="showPasswordModal" x-cloak
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 sm:p-7 relative" @click.outside="showPasswordModal = false">
                <div class="flex items-center justify-center w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>

                <h3 class="text-lg font-bold text-gray-800 text-center mt-4">
                    <span x-show="isNewUser">User Account Created!</span>
                    <span x-show="!isNewUser">New Password Generated</span>
                </h3>
                <p class="text-xs text-gray-500 text-center mt-1">
                    Login credentials for <strong class="text-gray-700" x-text="userName"></strong>
                </p>

                <div class="mt-5 space-y-3 bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <div>
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 block mb-0.5">Email / Username</span>
                        <p class="text-sm font-semibold text-gray-800 font-mono" x-text="userEmail"></p>
                    </div>

                    <div>
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 block mb-0.5">Initial Password</span>
                        <div class="flex items-center gap-2">
                            <input type="text"
                                   readonly
                                   x-model="password"
                                   class="flex-1 font-mono text-sm bg-white border border-gray-200 rounded-lg px-3 py-1.5 text-gray-800 select-all font-bold">
                            <button @click="copyPassword()" type="button"
                                    class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 shrink-0 transition">
                                <span x-show="!copiedPassword">Copy</span>
                                <span x-show="copiedPassword" x-cloak>Copied!</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex flex-col gap-2">
                    <button @click="copyAll()" type="button"
                            class="w-full py-2.5 px-4 text-xs font-bold rounded-lg border border-indigo-200 bg-indigo-50/70 text-indigo-700 hover:bg-indigo-100 transition flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                        </svg>
                        <span x-show="!copiedAll">Copy All Login Details to Clipboard</span>
                        <span x-show="copiedAll" x-cloak>All Credentials Copied!</span>
                    </button>
                </div>

                <p class="text-[11px] text-gray-400 mt-3 text-center leading-relaxed">
                    Please send these credentials to the user now. For security purposes, this password will not be shown again after closing this window.
                </p>

                <button @click="showPasswordModal = false" type="button"
                        class="mt-4 w-full py-2 text-center text-sm font-medium text-gray-500 hover:text-gray-700 transition border-t border-gray-100 pt-3">
                    Close Window
                </button>
            </div>
        </div>

        {{-- Modal: Confirm Reset Password --}}
        <div x-show="showConfirmReset" x-cloak
             x-transition:enter="ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 px-4">
            <div class="bg-white rounded-xl shadow-xl max-w-sm w-full p-6" @click.outside="showConfirmModal = false">
            <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full p-6" @click.outside="showConfirmReset = false">
                <div class="flex items-center justify-center w-12 h-12 rounded-full bg-amber-50 mx-auto">
                    <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m0 3.75h.008v.008H12v-.008zM21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>

                <h3 class="text-lg font-semibold text-gray-800 text-center mt-4">Reset Password?</h3>
                <p class="text-sm text-gray-500 text-center mt-1">
                    Password lama untuk <span class="font-medium text-gray-700" x-text="confirmUserName"></span>
                    akan tidak berlaku lagi dan diganti dengan password baru.
                <h3 class="text-base font-bold text-gray-800 text-center mt-4">Reset Password?</h3>
                <p class="text-xs text-gray-500 text-center mt-1">
                    This will invalidate the current password for <strong class="text-gray-700" x-text="resetUserName"></strong> and generate a new temporary password.
                </p>

                <div class="mt-6 flex gap-3">
                    <button @click="showConfirmModal = false" type="button"
                            class="flex-1 py-2.5 text-sm font-medium rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">
                        Batal
                    <button @click="showConfirmReset = false" type="button"
                            class="flex-1 py-2 text-xs font-semibold rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                        Cancel
                    </button>

                    <form :action="confirmAction" method="POST" class="flex-1">
                    <form :action="resetAction" method="POST" class="flex-1">
                        @csrf
                        <button type="submit"
                                class="w-full py-2.5 text-sm font-medium rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">
                            Ya, Reset
                                class="w-full py-2 text-xs font-semibold rounded-lg bg-amber-600 text-white hover:bg-amber-700 transition">
                            Yes, Reset
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal: password hasil generate, hanya tampil sekali --}}
        <div x-show="showPasswordModal" x-cloak
        {{-- Modal: Confirm Delete User --}}
        <div x-show="showConfirmDelete" x-cloak
             x-transition:enter="ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 px-4">
            <div class="bg-white rounded-xl shadow-xl max-w-sm w-full p-6" @click.outside="showPasswordModal = false">
                <div class="flex items-center justify-center w-12 h-12 rounded-full bg-green-50 mx-auto">
                    <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full p-6" @click.outside="showConfirmDelete = false">
                <div class="flex items-center justify-center w-12 h-12 rounded-full bg-red-50 mx-auto">
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>

                <h3 class="text-lg font-semibold text-gray-800 text-center mt-4">Password Baru Dibuat</h3>
                <p class="text-sm text-gray-500 text-center mt-1">
                    Untuk user: <span class="font-medium text-gray-700" x-text="userName"></span>
                <h3 class="text-base font-bold text-gray-800 text-center mt-4">Delete User Account?</h3>
                <p class="text-xs text-gray-500 text-center mt-1">
                    Are you sure you want to remove <strong class="text-gray-700" x-text="deleteUserName"></strong>? This user will immediately lose access to the system.
                </p>

                <div class="mt-4 flex items-center gap-2">
                    <input type="text" readonly x-model="password"
                           class="flex-1 font-mono text-sm rounded-lg border-gray-300 bg-gray-50">
                    <button @click="copy()" type="button"
                            class="px-3 py-2 text-sm font-medium rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 shrink-0">
                        <span x-show="!copied">Copy</span>
                        <span x-show="copied" x-cloak>Tersalin!</span>
                <div class="mt-6 flex gap-3">
                    <button @click="showConfirmDelete = false" type="button"
                            class="flex-1 py-2 text-xs font-semibold rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <form :action="deleteAction" method="POST" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="w-full py-2 text-xs font-semibold rounded-lg bg-red-600 text-white hover:bg-red-700 transition">
                            Yes, Delete
                        </button>
                    </form>
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
