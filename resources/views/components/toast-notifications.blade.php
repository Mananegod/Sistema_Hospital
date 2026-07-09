{{-- Contenedor global de notificaciones flotantes --}}
<div style="z-index: 9999;" class="fixed bottom-5 right-5 flex flex-col gap-3 max-w-sm w-full" x-data>
    <template x-for="msg in $store.toast.messages" :key="msg.id">
        <div x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             :class="{
                'bg-emerald-600 text-white': msg.type === 'success',
                'bg-red-600 text-white': msg.type === 'error',
                'bg-blue-600 text-white': msg.type === 'info'
             }"
             class="p-4 rounded-sm shadow-xl font-bold text-sm flex items-center justify-between gap-3 transform transition-all">
            
            <div class="flex items-center gap-2">
                <i :class="{
                    'fa-solid fa-circle-check': msg.type === 'success',
                    'fa-solid fa-circle-xmark': msg.type === 'error',
                    'fa-solid fa-circle-info': msg.type === 'info'
                }"></i>
                <span x-text="msg.text"></span>
            </div>

            <button @click="$store.toast.remove(msg.id)" class="opacity-70 hover:opacity-100 transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    </template>
</div>

{{-- Disparador automático para sesiones de Laravel (Flash Messages) --}}
@if(session('success'))
    <div x-data x-init="$nextTick(() => { $store.toast.add('{{ session('success') }}', 'success') })"></div>
@endif

@if(session('error'))
    <div x-data x-init="$nextTick(() => { $store.toast.add('{{ session('error') }}', 'error') })"></div>
@endif

@if(session('info'))
    <div x-data x-init="$nextTick(() => { $store.toast.add('{{ session('info') }}', 'info') })"></div>
@endif