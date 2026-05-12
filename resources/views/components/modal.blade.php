@props(['id', 'title' => 'Modal', 'maxWidth' => 'max-w-md'])

<div x-data
     x-show="$store.modal.isOpen('{{ $id }}')"
     x-cloak
     x-on:keydown.escape.window="$store.modal.close()"
     class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center">
        <div x-show="$store.modal.isOpen('{{ $id }}')"
             x-transition.opacity
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-all"
             @click="$store.modal.close()">
        </div>

        <div x-show="$store.modal.isOpen('{{ $id }}')"
             x-transition
             class="relative inline-block w-full {{ $maxWidth }} p-6 md:p-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl modern-rounded">
            
            <div class="flex justify-between items-center mb-5">
                <h3 class="text-xl font-extrabold text-slate-900">{{ $title }}</h3>
                <button @click="$store.modal.close()"
                        class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="space-y-5">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>