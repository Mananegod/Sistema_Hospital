<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión F15 - Pacientes Internados</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
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
<body class="antialiased text-slate-800 flex min-h-screen" x-data="{ openNuevo: false, openEdit: false, p: {} }">

    {{-- Sidebar --}}
    @include('sidebar')

    {{-- Contenido Principal --}}
    <main class="flex-1 p-8 h-screen overflow-y-auto">
        <div class="max-w-7xl mx-auto">
            
            {{-- Encabezado --}}
            <div class="flex justify-between items-end mb-8">
                <div>
                    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight tracking-tighter">PACIENTES INTERNADOS</h1>
                    <p class="text-slate-500 mt-1">Hospital Dr. Tiburcio Garrido</p>
                </div>
                <div class="flex gap-3">
                    <button class="bg-white border border-slate-200 text-slate-700 px-4 py-2 rounded-xl font-bold hover:bg-slate-50 transition shadow-sm">
                        <i class="fa-solid fa-print"></i> Reporte
                    </button>
                    {{-- Botón que abre el modal --}}
                    <button @click="openNuevo = true" class="bg-blue-600 text-white px-6 py-2 rounded-xl font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-200 flex items-center gap-2">
                        <i class="fa-solid fa-plus"></i> Nuevo Paciente
                    </button>
                </div>
            </div>

            {{-- Tabla (Estructura base) --}}
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50/50 text-slate-400 text-[10px] uppercase font-bold tracking-widest border-b border-slate-100">
                            <th class="px-8 py-5 text-slate-900">Nº Comprobante</th>
                            <th class="px-6 py-5">Paciente / Cédula</th>
                            <th class="px-6 py-5">Servicio / Cod</th>
                            <th class="px-6 py-5">Ubicación</th>
                            <th class="px-8 py-5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr class="hover:bg-blue-50/30 transition-colors group">
                            <td class="px-8 py-6">
                                <span class="bg-red-100 text-red-600 px-3 py-1 rounded-md font-black text-xs">#F15-001</span>
                            </td>
                            <td class="px-6 py-6">
                                <div class="font-bold text-sm text-slate-700 uppercase">Maria Rodriguez</div>
                                <div class="text-[10px] font-black text-slate-400">V-12.345.678</div>
                            </td>
                            <td class="px-6 py-6">
                                <div class="text-xs font-bold text-slate-600">Medicina Mujeres</div>
                                <div class="text-[10px] text-blue-500 font-bold uppercase">COD: 53</div>
                            </td>
                            <td class="px-6 py-6">
                                <span class="text-xs font-semibold text-slate-500"><i class="fa-solid fa-bed mr-1 opacity-50"></i> Sala 2 - Cama 04</span>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <button class="text-slate-300 hover:text-blue-600 transition"><i class="fa-solid fa-chevron-right"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    {{-- MODAL: NUEVO INGRESO DE PACIENTE --}}
    <div x-show="openNuevo" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         x-cloak>
        
        <div @click.away="openNuevo = false" class="bg-white w-full max-w-6xl rounded-[2.5rem] shadow-2xl p-10 overflow-hidden relative">
            {{-- Botón Cerrar --}}
            <button @click="openNuevo = false" class="absolute top-8 right-8 text-slate-400 hover:text-slate-600 transition">
                <i class="fa-solid fa-xmark text-2xl"></i>
            </button>

            <div class="flex items-center gap-3 mb-8 text-blue-600">
                <i class="fa-solid fa-file-medical text-2xl"></i>
                <h2 class="text-xs font-black uppercase tracking-[0.2em]">Nuevo Ingreso de Paciente</h2>
            </div>

            <form action="#" method="POST" class="space-y-8" @submit.prevent="openNuevo = false">
                {{-- Fila 1: Datos Personales --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-slate-400 px-1 uppercase tracking-widest">Cédula del Paciente</label>
                        <input type="text" placeholder="Ej: V-00.000.000" class="w-full bg-slate-50 p-4 rounded-2xl border-none text-sm font-semibold focus:ring-2 focus:ring-blue-500/20 transition-all outline-none">
                    </div>
                    <div class="md:col-span-2 space-y-2">
                        <label class="text-[10px] font-bold text-slate-400 px-1 uppercase tracking-widest">Nombre Completo (Apellidos y Nombres)</label>
                        <input type="text" placeholder="Nombre del paciente..." class="w-full bg-slate-50 p-4 rounded-2xl border-none text-sm font-semibold focus:ring-2 focus:ring-blue-500/20 transition-all outline-none">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-slate-400 px-1 uppercase tracking-widest">Edad</label>
                        <input type="number" placeholder="0" class="w-full bg-slate-50 p-4 rounded-2xl border-none text-sm font-semibold focus:ring-2 focus:ring-blue-500/20 transition-all outline-none">
                    </div>
                </div>

                {{-- Fila 2: Datos de Servicio --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 border-t border-slate-50 pt-8">
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-blue-500 px-1 uppercase tracking-widest">Cod. Servicio (53/54)</label>
                        <input type="text" placeholder="Ej: 53" class="w-full bg-blue-50/50 p-4 rounded-2xl border-none text-sm font-bold text-blue-700 outline-none focus:ring-2 focus:ring-blue-500/20">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-slate-400 px-1 uppercase tracking-widest">Servicio Destinatario</label>
                        <input type="text" placeholder="Ej: Medicina Mujeres" class="w-full bg-slate-50 p-4 rounded-2xl border-none text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500/20">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-slate-400 px-1 uppercase tracking-widest">Sala / Cama</label>
                        <input type="text" placeholder="Ej: Sala 2 - Cama 04" class="w-full bg-slate-50 p-4 rounded-2xl border-none text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500/20">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-red-500 px-1 uppercase tracking-widest">Nº Comprobante</label>
                        <input type="text" placeholder="Nº de comprobante" class="w-full bg-red-50/50 p-4 rounded-2xl border-none text-sm font-bold text-red-700 outline-none focus:ring-2 focus:ring-red-500/20">
                    </div>
                </div>

                {{-- Fila 3: Diagnóstico y Fecha --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="md:col-span-3 space-y-2">
                        <label class="text-[10px] font-bold text-slate-400 px-1 uppercase tracking-widest">Diagnóstico Inicial (Dx)</label>
                        <input type="text" placeholder="Indique el diagnóstico..." class="w-full bg-slate-50 p-4 rounded-2xl border-none text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500/20">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-slate-400 px-1 uppercase tracking-widest">Fecha Ingreso</label>
                        <input type="date" value="{{ date('Y-m-d') }}" class="w-full bg-slate-50 p-4 rounded-2xl border-none text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500/20">
                    </div>
                </div>

                {{-- Botón de Acción --}}
                <div class="flex justify-end pt-4">
                    <button type="submit" class="bg-slate-900 text-white px-10 py-5 rounded-2xl font-bold text-xs uppercase tracking-[0.2em] hover:bg-blue-600 transition-all shadow-xl shadow-slate-200 active:scale-95">
                        Registrar Ingreso y F15
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>