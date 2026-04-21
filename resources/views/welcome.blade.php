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

    <aside class="w-72 bg-slate-900 text-white flex-shrink-0 sticky top-0 h-screen flex flex-col shadow-2xl">
        <div class="p-8">
            <div class="flex items-center gap-3 mb-10">
                <div class="bg-blue-600 p-2 rounded-xl shadow-lg shadow-blue-500/20">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
                <span class="text-xl font-bold tracking-tight text-white">HOSPITAL <span class="text-blue-500">TG</span></span>
            </div>
            
            <nav class="space-y-3">
                <a href="{{ route('home') }}" class="flex items-center gap-4 px-4 py-3.5 rounded-2xl text-slate-400 hover:bg-slate-800 hover:text-white transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    <span class="font-semibold">Inicio</span>
                </a>

                <a href="{{ route('medicamentos.index') }}" class="flex items-center gap-4 px-4 py-3.5 rounded-2xl bg-blue-600 text-white shadow-xl shadow-blue-900/40 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    <span class="font-semibold">Inventario</span>
                </a>
            </nav>
        </div>

        <div class="mt-auto p-8 border-t border-slate-800 bg-slate-900/50">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-full bg-blue-500/20 border border-blue-500/40 flex items-center justify-center text-blue-400 font-bold">DC</div>
                <div>
                    <p class="text-sm font-bold text-white">David Camacho</p>
                    <p class="text-xs text-slate-500">Administrador</p>
                </div>
            </div>
            <a href="{{ url('/') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-400 hover:bg-red-500/10 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                <span class="font-semibold">Cerrar Sesión</span>
            </a>
        </div>
    </aside>

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
                            <span class="w-2 h-6 bg-blue-600 rounded-full"></span> Nuevo Medicamento
                        </h2>
                        <form action="{{ route('medicamentos.store') }}" method="POST" class="space-y-5">
                            @csrf
                            <input type="text" name="codigo_lote" required class="w-full bg-slate-50 border-none modern-rounded px-4 py-3.5 input-shadow" placeholder="Lote">
                            <input type="text" name="nombre_medicamento" required class="w-full bg-slate-50 border-none modern-rounded px-4 py-3.5 input-shadow" placeholder="Nombre">
                            <input type="number" name="cantidad_stock" required class="w-full bg-slate-50 border-none modern-rounded px-4 py-3.5 input-shadow" placeholder="Cantidad">
                            <select name="area_destino" class="w-full bg-slate-50 border-none modern-rounded px-4 py-3.5 input-shadow">
                                <option value="Urgencias">Urgencias</option>
                                <option value="Pediatría">Pediatría</option>
                                <option value="UCI">UCI</option>
                                <option value="Quirófano">Quirófano</option>
                            </select>
                            <input type="date" name="fecha_vencimiento" required class="w-full bg-slate-50 border-none modern-rounded px-4 py-3.5 input-shadow">
                            <button type="submit" class="w-full bg-slate-900 text-white font-bold py-4 modern-rounded shadow-lg hover:bg-slate-800 transition-all">Guardar</button>
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

    </body>
</html>