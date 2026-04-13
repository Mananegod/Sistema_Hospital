<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario de Medicamentos - Dr. Tiburcio Garrido</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #F7F9FB; }
            
        .color-primary-text { color: #1E293B; } /* Gris muy oscuro */
        .color-secondary-text { color: #64748B; } /* Gris medio */
        .color-area-urgencias { color: #F44336; } /* Rojo */
        .color-area-pediatria { color: #FF9800; } /* Naranja */
        .color-area-uci { color: #2196F3; } /* Azul */
        
        .modern-rounded { border-radius: 1rem; }
        .input-shadow { box-shadow: 0px 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .bg-card { background-color: #FFFFFF; }
        .bg-main { background-color: #F7F9FB; }
        
        input:focus, select:focus {
            border-color: #B2C3D3; /* Un gris-azul suave */
            box-shadow: 0 0 0 2px rgba(178, 195, 211, 0.2);
        }
    </style>
</head>
<body class="bg-main antialiased color-primary-text">

    <header class="bg-card px-8 py-5 border-b border-gray-100 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <button class="text-gray-400 hover:color-primary-text">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </button>
            <h1 class="text-2xl font-bold">Inventario de Medicamentos</h1>
        </div>
        <div class="color-secondary-text text-sm flex items-center gap-2">
            <span>Responsable:</span>
            <span class="color-primary-text font-semibold">Farm. Guardia</span>
        </div>
    </header>

    <main class="p-8 grid grid-cols-1 md:grid-cols-3 gap-8">

        <div class="md:col-span-1">
            <div class="bg-card p-8 border border-gray-100 modern-rounded shadow-sm">
                
                <div class="flex items-center gap-4 mb-8">
                    <span class="flex items-center justify-center h-8 w-8 bg-blue-50 text-blue-600 rounded-full font-bold text-lg">1</span>
                    <h2 class="text-xl font-semibold">Registrar Nuevo Lote</h2>
                </div>

<form action="{{ route('medicamentos.store') }}" method="POST" class="space-y-4">
    @csrf <div>
        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Código de Lote</label>
        <input type="text" name="codigo_lote" class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 transition-all" placeholder="Ej: LOT-2026-001" required>
    </div>

    <div>
        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Nombre del Medicamento</label>
        <input type="text" name="nombre_medicamento" class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 transition-all" placeholder="Ej: Amoxicilina 500mg" required>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Cantidad</label>
            <input type="number" name="cantidad_stock" class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 transition-all" value="0" required>
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Área Destino</label>
            <select name="area_destino" class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 transition-all">
                <option value="Emergencia">Emergencia</option>
                <option value="Pediatría">Pediatría</option>
                <option value="UCI">UCI</option>
                <option value="Quirófano">Quirófano</option>
            </select>
        </div>
    </div>

    <div>
        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Fecha Vencimiento</label>
        <input type="date" name="fecha_vencimiento" class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 transition-all" required>
    </div>

    <button type="submit" class="w-full bg-slate-900 text-white font-bold py-4 rounded-xl shadow-lg shadow-slate-200 hover:bg-slate-800 transition-all flex items-center justify-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
        Confirmar Registro
    </button>
</form>
            </div>
        </div>

        <div class="md:col-span-2">
            <div class="bg-card p-8 border border-gray-100 modern-rounded shadow-sm h-full">
                
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-xl font-semibold">Inventario de Medicamentos</h2>
                    <a href="{{ route('medicamentos.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-800">Ver todo</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="border-b border-gray-100">
                            <tr>
                                <th class="pb-4 color-secondary-text text-sm font-semibold">LOTE</th>
                                <th class="pb-4 color-secondary-text text-sm font-semibold">MEDICAMENTO</th>
                                <th class="pb-4 color-secondary-text text-sm font-semibold">CANT.</th>
                                <th class="pb-4 color-secondary-text text-sm font-semibold">ÁREA</th>
                                <th class="pb-4 color-secondary-text text-sm font-semibold">STATUS</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 text-sm">
                            @foreach($medicamentos as $med)
                            <tr class="hover:bg-gray-50/50">
                                <td class="py-5 color-secondary-text font-mono">{{ $med->codigo_lote }}</td>
                                <td class="py-5 font-semibold text-gray-900">{{ $med->nombre_medicamento }}</td>
                                
                                <td class="py-5 modern-rounded flex items-center justify-center h-10 w-10 text-center font-semibold border {{ $med->cantidad_stock < 10 ? 'border-red-100 bg-red-50 text-red-600' : 'border-gray-100 bg-gray-50 text-gray-900' }}">
                                    {{ $med->cantidad_stock }}
                                </td>

                                <td class="py-5 font-semibold {{ $med->area_destino == 'Urgencias' ? 'color-area-urgencias' : ($med->area_destino == 'Pediatría' ? 'color-area-pediatria' : 'color-area-uci') }}">
                                    {{ $med->area_destino }}
                                </td>

                                <td class="py-5 font-semibold text-success flex items-center gap-2">
                                    <span class="flex-shrink-0 h-2 w-2 rounded-full {{ $med->cantidad_stock > 0 ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                                    <span class="{{ $med->cantidad_stock > 0 ? 'text-emerald-700' : 'text-gray-500' }}">
                                        {{ $med->cantidad_stock > 0 ? 'Disponible' : 'Agotado' }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </main>

</body>
</html>