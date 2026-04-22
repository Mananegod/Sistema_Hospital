
<x-modal id="globalConfirm" title="Confirmar acción" maxWidth="max-w-md">
    <div class="space-y-4">
        <p class="text-slate-700" x-text="$store.confirm.message"></p>
    </div>
    <div class="flex gap-3 mt-6">
        <button @click="$store.confirm.cancel()" 
                class="flex-1 bg-slate-200 text-slate-700 font-bold py-3 rounded-xl transition hover:bg-slate-300">
            Cancelar
        </button>
        <button @click="$store.confirm.confirm()" 
                class="flex-1 bg-gray-600 text-white font-bold py-3 rounded-xl shadow transition hover:bg-gray-800">
            Confirmar
        </button>
    </div>
</x-modal>