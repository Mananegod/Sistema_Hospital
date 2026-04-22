<div x-data class="fixed top-5 right-5 z-50 flex flex-col items-end gap-3 max-w-[calc(100%-2rem)] sm:max-w-sm">
    <template x-for="toast in $store.toast.messages" :key="toast.id">
        <div x-show="true" x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 translate-y-[-20px] scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-[-20px] scale-95"
            class="w-full bg-white border-l-4 rounded-xl shadow-xl overflow-hidden" :class="{
                'border-emerald-500': toast.type === 'success',
                'border-red-500': toast.type === 'error',
                'border-blue-500': toast.type === 'info'
             }">
            <div class="p-4 flex items-start gap-3">
                <div class="flex-shrink-0">
                    <svg x-show="toast.type === 'success'" class="w-5 h-5 text-emerald-500" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <svg x-show="toast.type === 'error'" class="w-5 h-5 text-red-500" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <svg x-show="toast.type === 'info'" class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-slate-800" x-text="toast.message"></p>
                </div>
                <button @click="$store.toast.remove(toast.id)" class="text-slate-400 hover:text-slate-600 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
        </div>
    </template>
</div>