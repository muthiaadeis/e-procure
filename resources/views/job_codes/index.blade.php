<x-app-layout>
    <x-slot name="header">
        <h1 class="font-bold text-2xl text-gray-800 leading-tight">Job Code List</h1>
        <p class="text-sm text-gray-500 mt-1">Manage job codes and unit price references.</p>
    </x-slot>

    <div class="py-8" x-data="{
            confirmOpen: false,
            confirmTitle: '',
            confirmMessage: '',
            confirmFormId: null,
            openConfirm(title, message, formId) {
                this.confirmTitle = title;
                this.confirmMessage = message;
                this.confirmFormId = formId;
                this.confirmOpen = true;
            },
            submitConfirm() {
                this.confirmOpen = false;
                document.getElementById(this.confirmFormId).submit();
            }
        }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div x-data="{ show: true }"
                     x-show="show"
                     x-init="setTimeout(() => show = false, 4000)"
                     x-transition
                     class="mb-4 flex items-center gap-3 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm shadow-sm">
                    <span class="flex-shrink-0 w-7 h-7 rounded-full bg-green-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </span>
                    <span class="flex-1 font-medium">{{ session('success') }}</span>
                    <button type="button" @click="show = false" class="flex-shrink-0 text-green-500 hover:text-green-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            @endif

                        <div class="bg-white shadow-sm rounded-xl p-6"
                 x-data="{
                    q: {{ Js::from($search ?? '') }},
                    loading: false,
                    controller: null,
                    timer: null,
                    baseUrl: {{ Js::from(route('job-codes.index')) }},
                    runSearch() {
                        clearTimeout(this.timer);
                        this.timer = setTimeout(() => this.fetchResults(), 300);
                    },
                    fetchResults() {
                        if (this.controller) this.controller.abort();
                        this.controller = new AbortController();
                        this.loading = true;

                        const url = this.q ? `${this.baseUrl}?search=${encodeURIComponent(this.q)}` : this.baseUrl;

                        fetch(url, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' },
                            signal: this.controller.signal,
                        })
                        .then(res => res.text())
                        .then(html => {
                            document.getElementById('job-code-results').innerHTML = html;
                            window.history.replaceState({}, '', url);
                            this.loading = false;
                        })
                        .catch(e => {
                            if (e.name !== 'AbortError') this.loading = false;
                        });
                    },
                    clearSearch() {
                        this.q = '';
                        this.fetchResults();
                    }
                 }">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <div class="relative flex-1 sm:flex-none">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
                                </svg>
                            </span>
                            <input type="text"
                                   placeholder="Search job code, description, or part number..."
                                   x-model="q"
                                   x-on:input="runSearch()"
                                   class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm w-full sm:w-72 pl-10 pr-8 py-2">
                            <button type="button"
                                    x-show="q && !loading"
                                    x-cloak
                                    x-on:click="clearSearch()"
                                    title="Clear search"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                            <span x-show="loading" x-cloak
                                  class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <svg class="w-4 h-4 text-indigo-400 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                            </span>
                        </div>
                    </div>

                    <a href="{{ route('job-codes.create') }}"
                       class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition shadow-sm shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Job Code
                    </a>
                </div>

                <div id="job-code-results" class="relative">
                    @include('job_codes._table', ['jobCodes' => $jobCodes, 'search' => $search])
                </div>
            </div>
        </div>

        {{-- Modal konfirmasi hapus --}}
        <div x-show="confirmOpen"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center px-4"
             style="display: none;">
            <div x-show="confirmOpen" x-transition @click="confirmOpen = false"
                 class="absolute inset-0 bg-gray-900/50"></div>

            <div x-show="confirmOpen" x-transition
                 class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 text-center">
                <div class="mx-auto mb-4 flex items-center justify-center w-14 h-14 rounded-full bg-red-100">
                    <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-1" x-text="confirmTitle"></h3>
                <p class="text-sm text-gray-500 mb-6" x-text="confirmMessage"></p>
                <div class="flex items-center justify-center gap-3">
                    <button type="button" @click="confirmOpen = false"
                            class="flex-1 px-4 py-2 rounded-lg text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 transition">
                        Cancel
                    </button>
                    <button type="button" @click="submitConfirm()"
                            class="flex-1 px-4 py-2 rounded-lg text-sm font-medium text-white bg-red-600 hover:bg-red-700 transition">
                        Yes, Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
