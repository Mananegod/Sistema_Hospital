<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario de Medicamentos - Dr. Tiburcio Garrido</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #F7F9FB; }
        .color-primary-text { color: #1E293B; }
        .color-secondary-text { color: #64748B; }
        .modern-rounded { border-radius: 1rem; }
        .input-shadow { box-shadow: 0px 4px 6px -1px rgba(0, 0, 0, 0.05); }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="antialiased text-slate-800 flex min-h-screen" x-data="{ openEdit: false, openView: false, currentMed: {} }">

    @include('sidebar')

    <main class="flex-1 overflow-y-auto p-10">
        <div class="max-w-6xl mx-auto">
            <div class="mb-10">
                <h1 class="text-3xl font-extrabold color-primary-text tracking-tight">Módulo de Inventario</h1>
                <p class="color-secondary-text mt-2 font-medium">Hospital Dr. Tiburcio Garrido</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                <div class="lg:col-span-4">
                    <div class="bg-white p-8 modern-rounded border border-slate-100 shadow-sm sticky top-10">
                        <h2 class="text-lg font-bold color-primary-text mb-6 flex items-center gap-2">
                            <span class="w-2 h-6 bg-blue-600 rounded-full"></span> Nuevo Registro
                        </h2>
                        <form action="{{ route('medicamentos.store') }}" method="POST" class="space-y-5">
                            @csrf
                            <input type="text" name="codigo_lote" required class="w-full bg-slate-50 border-none modern-rounded px-4 py-3.5 input-shadow" placeholder="Código de Lote">
                            <input type="text" name="nombre_medicamento" required class="w-full bg-slate-50 border-none modern-rounded px-4 py-3.5 input-shadow" placeholder="Nombre">
                            <input type="number" name="cantidad_stock" required class="w-full bg-slate-50 border-none modern-rounded px-4 py-3.5 input-shadow" placeholder="Cantidad Stock">
                            <select name="area_destino" class="w-full bg-slate-50 border-none modern-rounded px-4 py-3.5 input-shadow">
                                <option value="Urgencias">Urgencias</option>
                                <option value="Pediatría">Pediatría</option>
                                <option value="UCI">UCI</option>
                                <option value="Quirófano">Quirófano</option>
                            </select>
                            <input type="date" name="fecha_vencimiento" required class="w-full bg-slate-50 border-none modern-rounded px-4 py-3.5 input-shadow">
                            <button type="submit" class="w-full bg-slate-900 text-white font-bold py-4 modern-rounded shadow-lg hover:bg-slate-800 transition-all">Guardar Medicamento</button>
                        </form>
                    </div>
                </div>

                <div class="lg:col-span-8">
                    <div class="bg-white modern-rounded border border-slate-100 shadow-sm overflow-hidden">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-xs font-bold text-slate-400 uppercase border-b border-slate-50">
                                    <th class="px-8 py-5">Medicamento</th>
                                    <th class="px-6 py-5 text-center">Stock</th>
                                    <th class="px-8 py-5 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($medicamentos as $med)
                                <tr>
                                    <td class="px-8 py-6">
                                        <p class="font-bold color-primary-text">{{ $med->nombre_medicamento }}</p>
                                        <p class="text-xs color-secondary-text">Lote: {{ $med->codigo_lote }}</p>
                                    </td>
                                    <td class="px-6 py-6 text-center">
                                        <span class="inline-flex items-center justify-center h-10 w-10 rounded-xl font-bold {{ $med->cantidad_stock < 10 ? 'bg-red-50 text-red-600' : 'bg-slate-50 text-slate-700' }}">
                                            {{ $med->cantidad_stock }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-6 text-right flex justify-end gap-2">
                                        <button @click="openView = true; currentMed = {{ json_encode($med) }};" class="p-2 text-slate-500 hover:bg-slate-100 rounded-lg transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </button>
                                        <button @click="openEdit = true; currentMed = {{ json_encode($med) }};" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>
                                        <form action="{{ route('medicamentos.destroy', $med->id) }}" method="POST" onsubmit="return confirm('¿Eliminar registro?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div x-show="openView" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="openView = false"></div>
            <div class="relative inline-block w-full max-w-lg p-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl modern-rounded">
                <h3 class="text-xl font-bold color-primary-text mb-6">Detalles del Medicamento</h3>
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-slate-50 p-4 rounded-xl">
                            <p class="text-xs font-bold text-slate-400 uppercase mb-1">Lote</p>
                            <p class="font-semibold" x-text="currentMed.codigo_lote"></p>
                        </div>
                        <div class="bg-slate-50 p-4 rounded-xl">
                            <p class="text-xs font-bold text-slate-400 uppercase mb-1">Área</p>
                            <p class="font-semibold text-blue-600" x-text="currentMed.area_destino"></p>
                        </div>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-xl">
                        <p class="text-xs font-bold text-slate-400 uppercase mb-1">Nombre</p>
                        <p class="font-semibold text-lg" x-text="currentMed.nombre_medicamento"></p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-slate-50 p-4 rounded-xl">
                            <p class="text-xs font-bold text-slate-400 uppercase mb-1">Vencimiento</p>
                            <p class="font-semibold" x-text="currentMed.fecha_vencimiento"></p>
                        </div>
                        <div class="bg-slate-50 p-4 rounded-xl">
                            <p class="text-xs font-bold text-slate-400 uppercase mb-1">Stock Actual</p>
                            <p class="font-bold text-2xl" x-text="currentMed.cantidad_stock"></p>
                        </div>
                    </div>
                </div>
                <button @click="openView = false" class="w-full mt-6 py-4 bg-slate-900 text-white font-bold rounded-xl shadow-lg">Cerrar Detalle</button>
            </div>
        </div>
    </div>

    <div x-show="openEdit" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="openEdit = false"></div>
            <div class="relative inline-block w-full max-w-md p-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl modern-rounded">
                <h3 class="text-xl font-bold color-primary-text mb-6">Actualizar Información</h3>
                <form :action="'/inventario/' + currentMed.id" method="POST" class="space-y-4">
                    @csrf @method('PUT')
                    <input type="text" name="nombre_medicamento" x-model="currentMed.nombre_medicamento" class="w-full bg-slate-50 border-none modern-rounded px-4 py-3.5 input-shadow font-semibold">
                    <input type="number" name="cantidad_stock" x-model="currentMed.cantidad_stock" class="w-full bg-slate-50 border-none modern-rounded px-4 py-3.5 input-shadow">
                    <select name="area_destino" x-model="currentMed.area_destino" class="w-full bg-slate-50 border-none modern-rounded px-4 py-3.5 input-shadow">
                        <option value="Urgencias">Urgencias</option>
                        <option value="Pediatría">Pediatría</option>
                        <option value="UCI">UCI</option>
                        <option value="Quirófano">Quirófano</option>
                    </select>
                    <div class="flex gap-3 pt-4">
                        <button type="button" @click="openEdit = false" class="flex-1 px-4 py-3 font-bold text-slate-500 bg-slate-100 rounded-xl">Cancelar</button>
                        <button type="submit" class="flex-1 px-4 py-3 font-bold text-white bg-blue-600 rounded-xl shadow-lg shadow-blue-200">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>