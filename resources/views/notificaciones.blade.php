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
        body { font-family: 'Inter', sans-serif; background-color: #F7F9FB; }
        [x-cloak] { display: none !important; }
    </style>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('sidebar', {
                isExpanded: true,
                isMobileOpen: false,
                toggleDesktop() { this.isExpanded = !this.isExpanded },
                closeMobile() { this.isMobileOpen = false }
            });
        })
    </script>
</head>
<body class="antialiased text-slate-800 flex min-h-screen">

    {{-- 1. Incluimos el Sidebar --}}
    @include('sidebar')

    {{-- 2. Contenido Principal con flex-1 para que respete el espacio del sidebar --}}
    <main class="flex-1 p-8 h-screen overflow-y-auto">
        <div class="max-w-7xl mx-auto">
            
            {{-- Encabezado --}}
            <div class="flex justify-between items-start mb-8">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tighter uppercase">Alertas de Inventario</h1>
                    <p class="text-slate-500 font-medium text-sm">Reporte de insumos críticos y fechas de vencimiento</p>
                </div>
                <div class="bg-red-50 px-4 py-2 rounded-xl border border-red-100 flex items-center gap-3">
                    <div class="w-2 h-2 bg-red-500 rounded-full animate-ping"></div>
                    <span class="text-[10px] font-black text-red-600 uppercase tracking-widest">3 Alertas Críticas hoy</span>
                </div>
            </div>

            {{-- Resumen de Estados --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 transition-all hover:shadow-md">
                    <div class="w-12 h-12 bg-red-100 text-red-600 rounded-2xl flex items-center justify-center mb-4 text-xl shadow-inner">
                        <i class="fa-solid fa-box-archive"></i>
                    </div>
                    <h3 class="text-2xl font-black text-slate-900">12</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Stock Crítico</p>
                </div>
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 transition-all hover:shadow-md">
                    <div class="w-12 h-12 bg-orange-100 text-orange-600 rounded-2xl flex items-center justify-center mb-4 text-xl shadow-inner">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                    <h3 class="text-2xl font-black text-slate-900">05</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Por Vencer (30 días)</p>
                </div>
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 transition-all hover:shadow-md">
                    <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center mb-4 text-xl shadow-inner">
                        <i class="fa-solid fa-check-double"></i>
                    </div>
                    <h3 class="text-2xl font-black text-slate-900">142</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Insumos Estables</p>
                </div>
            </div>

            {{-- Listado de Alertas --}}
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-8 border-b border-slate-50 flex justify-between items-center">
                    <h2 class="text-xs font-black uppercase tracking-widest text-slate-700">Detalle de Insumos en Riesgo</h2>
                    <div class="flex gap-2">
                        <button class="px-4 py-1.5 bg-slate-900 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest">Todos</button>
                        <button class="px-4 py-1.5 bg-slate-100 text-slate-500 rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-slate-200 transition">Solo Críticos</button>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">
                                <th class="px-8 py-5">Medicamento / Insumo</th>
                                <th class="px-6 py-5">Ubicación</th>
                                <th class="px-6 py-5">Stock</th>
                                <th class="px-6 py-5">Vencimiento</th>
                                <th class="px-8 py-5 text-right">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <td class="px-8 py-6">
                                    <div class="font-bold text-slate-900 uppercase">Omeprazol 40mg (Iny)</div>
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Lote: #2024-AFG</div>
                                </td>
                                <td class="px-6 py-6 font-semibold text-xs text-slate-500">Farmacia Central</td>
                                <td class="px-6 py-6">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-black text-red-600">02</span>
                                        <span class="text-[10px] font-bold text-slate-400 uppercase">Unidades</span>
                                    </div>
                                </td>
                                <td class="px-6 py-6 font-mono text-xs text-slate-600 font-bold">15/08/2026</td>
                                <td class="px-8 py-6 text-right">
                                    <span class="bg-red-100 text-red-600 px-3 py-1 rounded-lg text-[9px] font-black uppercase italic tracking-widest border border-red-200 shadow-sm">Crítico</span>
                                </td>
                            </tr>
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <td class="px-8 py-6">
                                    <div class="font-bold text-slate-900 uppercase">Solución Fisiológica 0.9%</div>
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Lote: #EXP-992</div>
                                </td>
                                <td class="px-6 py-6 font-semibold text-xs text-slate-500">Almacén de Emergencia</td>
                                <td class="px-6 py-6">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-black text-slate-700">150</span>
                                        <span class="text-[10px] font-bold text-slate-400 uppercase">Unidades</span>
                                    </div>
                                </td>
                                <td class="px-6 py-6">
                                    <div class="font-mono text-xs text-orange-600 font-bold">12/05/2026</div>
                                    <div class="text-[9px] font-bold text-orange-400 uppercase">En 12 días</div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <span class="bg-orange-100 text-orange-600 px-3 py-1 rounded-lg text-[9px] font-black uppercase italic tracking-widest border border-orange-200 shadow-sm">Por Vencer</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

</body>
</html>