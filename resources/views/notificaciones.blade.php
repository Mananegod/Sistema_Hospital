<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alertas de Inventario - Hospital TG</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap');
        * { font-family: 'Inter', sans-serif; }
        body { background-color: #F7F9FB; }
        [x-cloak] { display: none !important; }
        
        /* Scrollbar limpio para el contenedor */
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 9999px; }
    </style>
</head>
<body class="antialiased text-slate-600 bg-[#F7F9FB]">

    {{-- Estructura de Layout Principal adaptada al Sidebar Global --}}
    <div class="flex h-screen overflow-hidden w-full" x-data>
        
        {{-- Aquí Laravel inyecta automáticamente tu Sidebar --}}
        @include('sidebar')

        {{-- Contenedor del Contenido de Alertas --}}
        <div class="flex flex-col flex-1 min-w-0 overflow-hidden relative">
            
            {{-- Header para Móviles (Visible solo en pantallas pequeñas) --}}
            <header class="lg:hidden flex items-center justify-between p-4 bg-slate-900 text-white shadow-md z-30 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="bg-blue-600 p-2 rounded-xl shadow-lg shadow-blue-500/20 shrink-0">
                        <i class="fa-solid fa-hospital text-white"></i>
                    </div>
                    <span class="text-xl font-bold tracking-tight">HOSPITAL <span class="text-blue-500">TG</span></span>
                </div>
                <button @click="$store.sidebar.toggleMobile()" class="p-2 text-slate-400 hover:text-white rounded-lg focus:outline-none">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
            </header>

            {{-- Cuerpo Principal con Scroll Independiente --}}
            <main class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-10 w-full relative custom-scrollbar">
                
                {{-- Encabezado del Módulo --}}
                <div class="max-w-7xl mx-auto mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-3">
                            Panel de Alertas Preventivas
                        </h1>
                        <p class="text-slate-500 mt-1">Monitoreo automático de stock e indicadores de vencimiento de medicamentos.</p>
                    </div>
                    <div class="text-xs font-semibold text-slate-400 bg-white border border-slate-100 px-4 py-2 rounded-xl shadow-sm self-start md:self-auto">
                        <i class="fa-regular fa-clock mr-1.5"></i> Último cálculo: Hoy, {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                    </div>
                </div>

                <div class="max-w-7xl mx-auto space-y-8">
                    
                    {{-- Bloque de Tarjetas Estadísticas Dinámicas --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        
                        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4 transition-transform hover:scale-[1.01]">
                            <div class="bg-red-50 p-4 rounded-xl text-red-600 border border-red-100/50">
                                <i class="fa-solid fa-triangle-exclamation text-2xl"></i>
                            </div>
                            <div>
                                <div class="text-2xl font-black text-slate-800 tracking-tight">{{ $totalCriticos }}</div>
                                <div class="text-xs font-bold text-slate-400 uppercase tracking-tight">Stock Mínimo o Crítico</div>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4 transition-transform hover:scale-[1.01]">
                            <div class="bg-rose-50 p-4 rounded-xl text-rose-600 border border-rose-100/50">
                                <i class="fa-solid fa-skull-crossbones text-2xl"></i>
                            </div>
                            <div>
                                <div class="text-2xl font-black text-slate-800 tracking-tight">{{ $totalVencidos }}</div>
                                <div class="text-xs font-bold text-slate-400 uppercase tracking-tight">Lotes Expirados</div>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4 transition-transform hover:scale-[1.01]">
                            <div class="bg-orange-50 p-4 rounded-xl text-orange-600 border border-orange-100/50">
                                <i class="fa-solid fa-hourglass-half text-2xl"></i>
                            </div>
                            <div>
                                <div class="text-2xl font-black text-slate-800 tracking-tight">{{ $totalPorVencer }}</div>
                                <div class="text-xs font-bold text-slate-400 uppercase tracking-tight">Por Vencer (&lt; 30 días)</div>
                            </div>
                        </div>

                    </div>

                    {{-- Sección 1: Alertas de Stock Insuficiente --}}
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-slate-50 flex items-center justify-between bg-slate-50/40">
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-lg bg-red-100 text-red-600 flex items-center justify-center text-sm font-bold shadow-sm">
                                    <i class="fa-solid fa-boxes-stacked"></i>
                                </div>
                                <div>
                                    <h2 class="text-base font-bold text-slate-800">Medicamentos con Stock Insuficiente</h2>
                                    <p class="text-xs text-slate-400">Productos cuyas cantidades actuales están por debajo del umbral de reorden.</p>
                                </div>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50/70 border-b border-slate-100">
                                        <th class="px-6 py-4 text-xs font-bold uppercase text-slate-400 tracking-wider">Medicamento</th>
                                        <th class="px-6 py-4 text-xs font-bold uppercase text-slate-400 tracking-wider">Área de Destino</th>
                                        <th class="px-6 py-4 text-xs font-bold uppercase text-slate-400 tracking-wider">Stock Físico</th>
                                        <th class="px-6 py-4 text-xs font-bold uppercase text-slate-400 tracking-wider">Límite Establecido</th>
                                        <th class="px-8 py-4 text-xs font-bold uppercase text-slate-400 tracking-wider text-right">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse($stockCritico as $item)
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-6 py-6">
                                                <div class="font-bold text-slate-800 text-sm">{{ $item->medicamento }}</div>
                                                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-tighter">ID Ref: #00{{ $item->id }}</div>
                                            </td>
                                            <td class="px-6 py-6 font-semibold text-xs text-slate-500">
                                                {{ $item->area_destino ?? 'Almacén General' }}
                                            </td>
                                            <td class="px-6 py-6">
                                                <span class="text-sm font-black text-red-600 bg-red-50 px-2.5 py-1 rounded-lg border border-red-100">
                                                    {{ $item->stock_actual }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-6 font-bold text-xs text-slate-400">
                                                {{ $stockMinimoEstandar }} unidades
                                            </td>
                                            <td class="px-8 py-6 text-right">
                                                <span class="bg-red-100 text-red-600 px-3 py-1 rounded-lg text-[9px] font-black uppercase italic tracking-widest border border-red-200 shadow-sm">
                                                    Reordenar
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-12 text-center text-slate-400 font-medium bg-slate-50/10">
                                                <div class="flex flex-col items-center justify-center gap-2">
                                                    <i class="fa-solid fa-circle-check text-green-500 text-2xl mb-1"></i>
                                                    <span class="text-slate-700 font-bold text-sm">¡Inventario Conforme!</span>
                                                    <p class="text-xs text-slate-400 max-w-xs">No se detectaron medicamentos con cantidades inferiores al stock crítico en este momento.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Sección 2: Alertas de Vencimiento Próximo --}}
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-slate-50 flex items-center justify-between bg-slate-50/40">
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center text-sm font-bold shadow-sm">
                                    <i class="fa-solid fa-calendar-circle-exclamation"></i>
                                </div>
                                <div>
                                    <h2 class="text-base font-bold text-slate-800">Cronograma de Vencimientos y Caducidad</h2>
                                    <p class="text-xs text-slate-400">Control de lotes críticos ordenados cronológicamente según su fecha de expiración.</p>
                                </div>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50/70 border-b border-slate-100">
                                        <th class="px-6 py-4 text-xs font-bold uppercase text-slate-400 tracking-wider">Medicamento</th>
                                        <th class="px-6 py-4 text-xs font-bold uppercase text-slate-400 tracking-wider">Ubicación / Área</th>
                                        <th class="px-6 py-4 text-xs font-bold uppercase text-slate-400 tracking-wider">Existencia Afectada</th>
                                        <th class="px-6 py-4 text-xs font-bold uppercase text-slate-400 tracking-wider">Fecha de Expiración</th>
                                        <th class="px-8 py-4 text-xs font-bold uppercase text-slate-400 tracking-wider text-right">Prioridad</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse($alertasVencimiento as $lote)
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-6 py-6">
                                                <div class="font-bold text-slate-800 text-sm">{{ $lote->medicamento }}</div>
                                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Medicamento en Lote</div>
                                            </td>
                                            <td class="px-6 py-6 font-semibold text-xs text-slate-500">
                                                {{ $lote->area_destino ?? 'Almacén Central' }}
                                            </td>
                                            <td class="px-6 py-6">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-sm font-black text-slate-700">{{ $lote->unidades }}</span>
                                                    <span class="text-[10px] font-bold text-slate-400 uppercase">Unidades</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-6">
                                                <div class="font-mono text-xs font-bold {{ $lote->estado_vencimiento === 'Vencido' ? 'text-rose-600' : 'text-orange-600' }}">
                                                    {{ \Carbon\Carbon::parse($lote->fecha_vencimiento)->format('d/m/Y') }}
                                                </div>
                                                <div class="text-[9px] font-bold {{ $lote->estado_vencimiento === 'Vencido' ? 'text-rose-400' : 'text-orange-400' }} uppercase">
                                                    @if($lote->estado_vencimiento === 'Vencido')
                                                        Vencido hace {{ abs((int)$lote->dias_restantes) }} días
                                                    @else
                                                        Vence en {{ $lote->dias_restantes }} días
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-8 py-6 text-right">
                                                @if($lote->estado_vencimiento === 'Vencido')
                                                    <span class="bg-rose-100 text-rose-600 px-3 py-1 rounded-lg text-[9px] font-black uppercase italic tracking-widest border border-rose-200 shadow-sm">
                                                        Vencido (Bloqueado)
                                                    </span>
                                                @else
                                                    <span class="bg-orange-100 text-orange-600 px-3 py-1 rounded-lg text-[9px] font-black uppercase italic tracking-widest border border-orange-200 shadow-sm">
                                                        Por Vencer
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-12 text-center text-slate-400 font-medium bg-slate-50/10">
                                                <div class="flex flex-col items-center justify-center gap-2">
                                                    <i class="fa-solid fa-shield-heart text-blue-500 text-2xl mb-1"></i>
                                                    <span class="text-slate-700 font-bold text-sm">¡Seguridad Garantizada!</span>
                                                    <p class="text-xs text-slate-400 max-w-xs">No existen lotes con fechas de vencimiento expiradas ni próximas a vencer dentro del rango de 30 días.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>

</body>
</html>