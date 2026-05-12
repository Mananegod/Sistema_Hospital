<div x-data x-show="$store.loading.active" x-cloak
     x-transition.opacity.duration.300ms
     class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-sm w-full mx-4 text-center"
         x-show="$store.loading.active"
         x-transition.scale.origin.center>
        
       
        <div x-show="!$store.loading.timedOut" class="flex justify-center mb-5">
            <div class="w-16 h-16 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin"></div>
        </div>
        
        <!-- Ícono de timeout -->
        <div x-show="$store.loading.timedOut" class="flex justify-center mb-5">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
        
        
        <p class="text-slate-700 font-medium mb-6" x-text="$store.loading.message"></p>
        
        <!-- Botón reintentar (solo en timeout) -->
        <button x-show="$store.loading.timedOut" 
                @click="$store.loading.retry()"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-xl transition shadow">
            Reintentar ahora
        </button>
        
        <!-- Texto de carga normal -->
        <p x-show="!$store.loading.timedOut" class="text-xs text-slate-400">Por favor espera, no cierres esta ventana</p>
    </div>
</div>