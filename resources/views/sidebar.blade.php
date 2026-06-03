{{-- 
    Sidebar Autónomo y Modernizado (Optimizado para entorno Offline)
    - Lógica de estado local encapsulada sin dependencias globales ($store)
    - Mapeo exacto de las rutas del sistema médico e inventario, incluyendo Epidemiología
--}}
<div x-data="{ expanded: true, mobileOpen: false }" 
     @toggle-sidebar.window="expanded = !expanded"
     @toggle-mobile-sidebar.window="mobileOpen = !mobileOpen"
     class="relative">

    {{-- Overlay oscuro para pantallas móviles --}}
    <div x-show="mobileOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="mobileOpen = false"
         class="fixed inset-0 bg-slate-950/80 z-40 lg:hidden backdrop-blur-sm" 
         x-cloak>
    </div>

    {{-- Sidebar Contenedor Principal --}}
    <aside :class="{
             'w-72': expanded,
             'w-20': !expanded,
             'translate-x-0': mobileOpen,
             '-translate-x-full': !mobileOpen
           }"
           class="fixed inset-y-0 left-0 z-50 flex flex-col h-screen bg-slate-900 text-white shadow-2xl transition-all duration-300 ease-in-out lg:relative lg:translate-x-0 group border-r border-slate-800">

        {{-- Header del Sidebar (Logo y Botón de colapso) --}}
        <div class="flex items-center justify-between p-5 h-20 border-b border-slate-800 shrink-0">
            <div class="flex items-center gap-3 overflow-hidden">
                <div class="bg-blue-600 p-2.5 rounded-xl shadow-lg shadow-blue-500/20 shrink-0 flex items-center justify-center">
                    <i class="fa-solid fa-hospital text-white text-lg"></i>
                </div>
                <span x-show="expanded" x-transition.opacity.duration.300ms class="text-xl font-bold tracking-tight whitespace-nowrap">
                    HOSPITAL <span class="text-blue-500">TG</span>
                </span>
            </div>
            
            {{-- Botón para colapsar (Solo visible en pantallas grandes) --}}
            <button @click="expanded = !expanded" class="hidden lg:flex items-center justify-center h-8 w-8 rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white transition-colors">
                <i class="fa-solid" :class="expanded ? 'fa-angle-left' : 'fa-angle-right'"></i>
            </button>
        </div>

        {{-- Cuerpo de Navegación con Scroll --}}
        <div class="flex-1 overflow-y-auto py-6 px-4 space-y-7 custom-scrollbar">
            
            {{-- Grupo 1: General --}}
            <div>
                <p x-show="expanded" x-transition.opacity class="text-[10px] font-bold uppercase tracking-widest text-slate-500 px-3 mb-3">
                    General
                </p>
                <ul class="space-y-1">
                    {{-- Inicio --}}
                    <li>
                        <a href="{{ route('home') }}" 
                           class="flex items-center h-12 rounded-xl transition-all px-4 gap-4 {{ request()->routeIs('home') ? 'bg-blue-600 text-white font-semibold shadow-lg shadow-blue-600/10' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"
                           :class="{'justify-start': expanded, 'justify-center px-0': !expanded}"
                           title="Inicio">
                            <div class="w-6 flex justify-center shrink-0">
                                <i class="fa-solid fa-house text-lg"></i>
                            </div>
                            <span x-show="expanded" x-transition.opacity.duration.200ms class="text-sm whitespace-nowrap">Inicio</span>
                        </a>
                    </li>
                    
                    {{-- Estadísticas --}}
                    <li>
                        <a href="{{ route('estadisticas.index') }}" 
                           class="flex items-center h-12 rounded-xl transition-all px-4 gap-4 {{ request()->routeIs('estadisticas.index') ? 'bg-blue-600 text-white font-semibold shadow-lg shadow-blue-600/10' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"
                           :class="{'justify-start': expanded, 'justify-center px-0': !expanded}"
                           title="Estadísticas">
                            <div class="w-6 flex justify-center shrink-0">
                                <i class="fa-solid fa-chart-pie text-lg"></i>
                            </div>
                            <span x-show="expanded" x-transition.opacity.duration.200ms class="text-sm whitespace-nowrap">Estadísticas</span>
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Grupo 2: Gestión Médica (ACTUALIZADO) --}}
            <div>
                <p x-show="expanded" x-transition.opacity class="text-[10px] font-bold uppercase tracking-widest text-slate-500 px-3 mb-3">
                    Gestión Médica
                </p>
                <ul class="space-y-1">
                    {{-- Pacientes --}}
                    <li>
                        <a href="{{ route('pacientes.index') }}" 
                           class="flex items-center h-12 rounded-xl transition-all px-4 gap-4 {{ request()->routeIs('pacientes.index') ? 'bg-blue-600 text-white font-semibold shadow-lg shadow-blue-600/10' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"
                           :class="{'justify-start': expanded, 'justify-center px-0': !expanded}"
                           title="Pacientes">
                            <div class="w-6 flex justify-center shrink-0">
                                <i class="fa-solid fa-user-injured text-lg"></i>
                            </div>
                            <span x-show="expanded" x-transition.opacity.duration.200ms class="text-sm whitespace-nowrap">Pacientes</span>
                        </a>
                    </li>

                    {{-- Vigilancia Epidemiológica (NUEVO ITEM) --}}
                    <li>
                        <a href="{{ route('epidemiologia.index') }}" 
                           class="flex items-center h-12 rounded-xl transition-all px-4 gap-4 {{ request()->routeIs('epidemiologia.index') ? 'bg-blue-600 text-white font-semibold shadow-lg shadow-blue-600/10' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"
                           :class="{'justify-start': expanded, 'justify-center px-0': !expanded}"
                           title="Epidemiología">
                            <div class="w-6 flex justify-center shrink-0">
                                <i class="fa-solid fa-shield-virus text-lg"></i>
                            </div>
                            <span x-show="expanded" x-transition.opacity.duration.200ms class="text-sm whitespace-nowrap">Epidemiología</span>
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Grupo 3: Almacén e Inventario --}}
            <div>
                <p x-show="expanded" x-transition.opacity class="text-[10px] font-bold uppercase tracking-widest text-slate-500 px-3 mb-3">
                    Almacén e Inventario
                </p>
                <ul class="space-y-1">
                    {{-- Almacén --}}
                    <li>
                        <a href="{{ route('almacen.index') }}" 
                           class="flex items-center h-12 rounded-xl transition-all px-4 gap-4 {{ request()->routeIs('almacen.index') ? 'bg-blue-600 text-white font-semibold shadow-lg shadow-blue-600/10' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"
                           :class="{'justify-start': expanded, 'justify-center px-0': !expanded}"
                           title="Almacén">
                            <div class="w-6 flex justify-center shrink-0">
                                <i class="fa-solid fa-box-archive text-lg"></i>
                            </div>
                            <span x-show="expanded" x-transition.opacity.duration.200ms class="text-sm whitespace-nowrap">Almacén</span>
                        </a>
                    </li>

                    {{-- Medicamentos --}}
                    <li>
                        <a href="{{ route('medicamentos.index') }}" 
                           class="flex items-center h-12 rounded-xl transition-all px-4 gap-4 {{ request()->routeIs('medicamentos.index') ? 'bg-blue-600 text-white font-semibold shadow-lg shadow-blue-600/10' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"
                           :class="{'justify-start': expanded, 'justify-center px-0': !expanded}"
                           title="Medicamentos">
                            <div class="w-6 flex justify-center shrink-0">
                                <i class="fa-solid fa-pills text-lg"></i>
                            </div>
                            <span x-show="expanded" x-transition.opacity.duration.200ms class="text-sm whitespace-nowrap">Medicamentos</span>
                        </a>
                    </li>

                    {{-- Retiros --}}
                    <li>
                        <a href="{{ route('retiros.index') }}" 
                           class="flex items-center h-12 rounded-xl transition-all px-4 gap-4 {{ request()->routeIs('retiros.index') ? 'bg-blue-600 text-white font-semibold shadow-lg shadow-blue-600/10' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"
                           :class="{'justify-start': expanded, 'justify-center px-0': !expanded}"
                           title="Retiros">
                            <div class="w-6 flex justify-center shrink-0">
                                <i class="fa-solid fa-hand-holding-medical text-lg"></i>
                            </div>
                            <span x-show="expanded" x-transition.opacity.duration.200ms class="text-sm whitespace-nowrap">Retiros</span>
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Grupo 4: Administración y Auditoría --}}
            <div>
                <p x-show="expanded" x-transition.opacity class="text-[10px] font-bold uppercase tracking-widest text-slate-500 px-3 mb-3">
                    Administración y Seguridad
                </p>
                <ul class="space-y-1">
                    {{-- Personal --}}
                    <li>
                        <a href="{{ route('personal.index') }}" 
                           class="flex items-center h-12 rounded-xl transition-all px-4 gap-4 {{ request()->routeIs('personal.index') ? 'bg-blue-600 text-white font-semibold shadow-lg shadow-blue-600/10' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"
                           :class="{'justify-start': expanded, 'justify-center px-0': !expanded}"
                           title="Personal">
                            <div class="w-6 flex justify-center shrink-0">
                                <i class="fa-solid fa-users text-lg"></i>
                            </div>
                            <span x-show="expanded" x-transition.opacity.duration.200ms class="text-sm whitespace-nowrap">Personal</span>
                        </a>
                    </li>

                    {{-- Bitácora --}}
                    <li>
                        <a href="{{ route('personal.bitacora') }}" 
                           class="flex items-center h-12 rounded-xl transition-all px-4 gap-4 {{ request()->routeIs('personal.bitacora') ? 'bg-blue-600 text-white font-semibold shadow-lg shadow-blue-600/10' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"
                           :class="{'justify-start': expanded, 'justify-center px-0': !expanded}"
                           title="Bitácora">
                            <div class="w-6 flex justify-center shrink-0">
                                <i class="fa-solid fa-clock-rotate-left text-lg"></i>
                            </div>
                            <span x-show="expanded" x-transition.opacity.duration.200ms class="text-sm whitespace-nowrap">Bitácora del Sistema</span>
                        </a>
                    </li>
                </ul>
            </div>

        </div>

        {{-- Footer de Usuario y Cierre de Sesión --}}
        <div class="p-4 border-t border-slate-800 bg-slate-950/30 space-y-4 shrink-0">
            
            {{-- Info Perfil del Usuario --}}
            <div class="flex items-center gap-3 rounded-xl transition-all"
                 :class="{'px-2': expanded, 'justify-center px-0': !expanded}">
                <div class="h-10 w-10 rounded-xl bg-blue-600/10 border border-blue-500/20 flex items-center justify-center font-black text-blue-400 tracking-tighter shrink-0 shadow-inner">
                    DC
                </div>
                <div x-show="expanded" x-transition.opacity class="whitespace-nowrap overflow-hidden transition-all duration-300">
                    <p class="text-sm font-bold text-slate-200 truncate">David Camacho</p>
                    <p class="text-xs text-slate-500 truncate">Administrador</p>
                </div>
            </div>

            {{-- Botón Cerrar Sesión --}}
            <a href="{{ url('/') }}"
               class="flex items-center h-12 rounded-xl text-red-400 hover:bg-red-500/10 bg-red-400/5 transition-all overflow-hidden"
               :class="{'px-4 gap-4 justify-start': expanded, 'px-0 justify-center': !expanded}"
               title="Cerrar Sesión">
                <div class="w-6 flex justify-center shrink-0">
                    <i class="fa-solid fa-arrow-right-from-bracket text-lg"></i>
                </div>
                <span class="font-semibold text-sm whitespace-nowrap" x-show="expanded" x-transition.opacity>Cerrar Sesión</span>
            </a>
        </div>
    </aside>
</div>

{{-- Estilos encapsulados para un scrollbar limpio y moderno --}}
<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #334155;
        border-radius: 9999px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #475569;
    }
</style>