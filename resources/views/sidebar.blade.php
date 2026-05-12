{{-- Overlay oscuro de fondo para pantallas móviles --}}
<div x-show="$store.sidebar.isMobileOpen" 
     x-transition.opacity 
     @click="$store.sidebar.closeMobile()"
     class="fixed inset-0 bg-slate-900/80 z-40 lg:hidden backdrop-blur-sm" 
     x-cloak>
</div>

{{-- Sidebar Contenedor Principal --}}
<aside :class="{
        'w-72': $store.sidebar.isExpanded,
        'w-20': !$store.sidebar.isExpanded,
        'translate-x-0': $store.sidebar.isMobileOpen,
        '-translate-x-full': !$store.sidebar.isMobileOpen
       }"
       class="fixed inset-y-0 left-0 z-50 flex flex-col h-screen bg-slate-900 text-white shadow-2xl transition-all duration-300 ease-in-out lg:relative lg:translate-x-0 group">

    {{-- Header del Sidebar (Logo y Botón Toggle) --}}
    <div class="flex items-center justify-between p-5 h-20 border-b border-slate-800 shrink-0">
        
        <div class="flex items-center gap-3 overflow-hidden" x-show="$store.sidebar.isExpanded" x-transition.opacity>
            <div class="bg-blue-600 p-2 rounded-xl shadow-lg shadow-blue-500/20 shrink-0">
                <i class="fa-solid fa-hospital text-white"></i>
            </div>
            <span class="text-xl font-bold tracking-tight whitespace-nowrap">HOSPITAL <span class="text-blue-500">TG</span></span>
        </div>

        {{-- Botón Toggle (Desktop) --}}
        <button @click="$store.sidebar.toggleDesktop()" 
                class="hidden lg:flex items-center justify-center w-10 h-10 rounded-xl bg-slate-800 hover:bg-slate-700 transition-colors text-slate-400 hover:text-white shrink-0"
                :class="{'mx-auto': !$store.sidebar.isExpanded}">
            <i class="fa-solid fa-bars-staggered transition-transform duration-300" :class="{'rotate-180': !$store.sidebar.isExpanded}"></i>
        </button>

        {{-- Botón Cerrar (Mobile) --}}
        <button @click="$store.sidebar.closeMobile()" class="lg:hidden p-2 text-slate-400 hover:text-white rounded-lg shrink-0">
            <i class="fa-solid fa-xmark text-2xl"></i>
        </button>
    </div>

    {{-- Body del Sidebar --}}
    <nav class="flex-1 overflow-y-auto overflow-x-hidden p-4 space-y-2 custom-scrollbar">
        
        {{-- Módulo Inicio --}}
        <a href="{{ route('home') }}"
           class="flex items-center h-12 rounded-2xl transition-all {{ request()->routeIs('home') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"
           :class="{'px-4 gap-4 justify-start': $store.sidebar.isExpanded, 'px-0 justify-center': !$store.sidebar.isExpanded}"
           title="Inicio">
            <div class="w-6 flex justify-center shrink-0"><i class="fa-solid fa-house text-lg"></i></div>
            <span class="font-semibold tracking-wide whitespace-nowrap" x-show="$store.sidebar.isExpanded" x-transition.opacity>Inicio</span>
        </a>

        {{-- Módulo Pacientes --}}
        <a href="{{ route('pacientes.index') }}"
           class="flex items-center h-12 rounded-2xl transition-all {{ request()->routeIs('pacientes.index') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"
           :class="{'px-4 gap-4 justify-start': $store.sidebar.isExpanded, 'px-0 justify-center': !$store.sidebar.isExpanded}"
           title="Pacientes">
            <div class="w-6 flex justify-center shrink-0"><i class="fa-solid fa-hospital-user text-lg"></i></div>
            <span class="font-semibold tracking-wide whitespace-nowrap" x-show="$store.sidebar.isExpanded" x-transition.opacity>Pacientes</span>
        </a>

        <div class="my-4 border-t border-slate-800/50"></div>

        {{-- Módulo Gestión de Almacén --}}
        <a href="{{ route('almacen.index') }}"
           class="flex items-center h-12 rounded-2xl transition-all {{ request()->routeIs('almacen.index') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"
           :class="{'px-4 gap-4 justify-start': $store.sidebar.isExpanded, 'px-0 justify-center': !$store.sidebar.isExpanded}"
           title="Gestión de Almacén">
            <div class="w-6 flex justify-center shrink-0"><i class="fa-solid fa-boxes-stacked text-lg"></i></div>
            <span class="font-semibold tracking-wide whitespace-nowrap" x-show="$store.sidebar.isExpanded" x-transition.opacity>Almacén</span>
        </a>

        {{-- Módulo Retiros --}}
        <a href="{{ route('retiros.index') }}"
           class="flex items-center h-12 rounded-2xl transition-all {{ request()->routeIs('retiros.index') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"
           :class="{'px-4 gap-4 justify-start': $store.sidebar.isExpanded, 'px-0 justify-center': !$store.sidebar.isExpanded}"
           title="Retiros de Insumos">
            <div class="w-6 flex justify-center shrink-0"><i class="fa-solid fa-hand-holding-medical text-lg"></i></div>
            <span class="font-semibold tracking-wide whitespace-nowrap" x-show="$store.sidebar.isExpanded" x-transition.opacity>Retiros</span>
        </a>

        {{-- Módulo Inventario Maestro --}}
        <a href="{{ route('medicamentos.index') }}"
           class="flex items-center h-12 rounded-2xl transition-all {{ request()->routeIs('medicamentos.index') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"
           :class="{'px-4 gap-4 justify-start': $store.sidebar.isExpanded, 'px-0 justify-center': !$store.sidebar.isExpanded}"
           title="Inventario Maestro">
            <div class="w-6 flex justify-center shrink-0"><i class="fa-solid fa-warehouse text-lg"></i></div>
            <span class="font-semibold tracking-wide whitespace-nowrap" x-show="$store.sidebar.isExpanded" x-transition.opacity>Medicamentos</span>
        </a>

        <div class="my-4 border-t border-slate-800/50"></div>

        {{-- Módulo Personal --}}
        <a href="{{ route('personal.index') }}"
           class="flex items-center h-12 rounded-2xl transition-all {{ request()->routeIs('personal.index') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"
           :class="{'px-4 gap-4 justify-start': $store.sidebar.isExpanded, 'px-0 justify-center': !$store.sidebar.isExpanded}"
           title="Personal">
            <div class="w-6 flex justify-center shrink-0"><i class="fa-solid fa-user-doctor text-lg"></i></div>
            <span class="font-semibold tracking-wide whitespace-nowrap" x-show="$store.sidebar.isExpanded" x-transition.opacity>Personal</span>
        </a>

        {{-- Módulo Bitácora --}}
        <a href="{{ route('personal.bitacora') }}"
           class="flex items-center h-12 rounded-2xl transition-all {{ request()->routeIs('personal.bitacora') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"
           :class="{'px-4 gap-4 justify-start': $store.sidebar.isExpanded, 'px-0 justify-center': !$store.sidebar.isExpanded}"
           title="Bitácora de Auditoría">
            <div class="w-6 flex justify-center shrink-0"><i class="fa-solid fa-clipboard-list text-lg"></i></div>
            <span class="font-semibold tracking-wide whitespace-nowrap" x-show="$store.sidebar.isExpanded" x-transition.opacity>Bitácora</span>
        </a>
    </nav>

    {{-- Footer del Sidebar (Perfil y Logout) --}}
    <div class="p-4 border-t border-slate-800 bg-slate-900/80 shrink-0">
        
        {{-- Ficha de Perfil --}}
        <div class="flex items-center mb-4 overflow-hidden" :class="{'gap-3 justify-start': $store.sidebar.isExpanded, 'justify-center': !$store.sidebar.isExpanded}">
            <div class="w-10 h-10 rounded-full bg-blue-500/20 border border-blue-500/40 flex items-center justify-center text-blue-400 font-bold text-xs shrink-0">
                DC
            </div>
            <div x-show="$store.sidebar.isExpanded" class="whitespace-nowrap overflow-hidden transition-opacity" x-transition.opacity>
                <p class="text-sm font-bold text-slate-200 truncate">David Camacho</p>
                <p class="text-xs text-slate-400 truncate">Administrador</p>
            </div>
        </div>

        {{-- Botón Cerrar Sesión --}}
        <a href="{{ url('/') }}"
           class="flex items-center h-12 rounded-xl text-red-400 hover:bg-red-500/10 bg-red-400/5 transition-all overflow-hidden"
           :class="{'px-4 gap-3 justify-start': $store.sidebar.isExpanded, 'px-0 justify-center': !$store.sidebar.isExpanded}"
           title="Cerrar Sesión">
            <div class="w-6 flex justify-center shrink-0">
                <i class="fa-solid fa-arrow-right-from-bracket text-lg"></i>
            </div>
            <span class="font-semibold text-sm whitespace-nowrap" x-show="$store.sidebar.isExpanded" x-transition.opacity>Cerrar Sesión</span>
        </a>
    </div>
</aside>

{{-- Estilos para scrollbar limpio --}}
<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 5px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(51, 65, 85, 0.5);
        border-radius: 10px;
    }
    .custom-scrollbar:hover::-webkit-scrollbar-thumb {
        background: rgba(71, 85, 105, 0.8);
    }
</style>