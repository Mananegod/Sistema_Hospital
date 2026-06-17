{{-- 
    Sidebar Autónomo, Modernizado y Fluido
    - Estado contraído/expandido persistente entre páginas (localStorage).
    - Prevención de FOUC (ancho definido por variable CSS desde el <head>).
    - Móvil: oculto hasta que se active el botón hamburguesa.
--}}
<div class="relative" x-data="{ ready: false }" x-init="setTimeout(() => ready = true, 50)">

    {{-- Overlay oscuro para pantallas móviles --}}
    <div x-show="$store.sidebar.mobileOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="$store.sidebar.toggleMobile()"
         class="fixed inset-0 bg-slate-950/80 z-40 lg:hidden backdrop-blur-sm" 
         x-cloak>
    </div>

    {{-- Sidebar Contenedor Principal --}}
    <aside :class="{
             'w-72': $store.sidebar.open,
             'w-20': !$store.sidebar.open,
             'translate-x-0': $store.sidebar.mobileOpen,
             '-translate-x-full': !$store.sidebar.mobileOpen,
             'transition-all duration-300 ease-in-out': ready
           }"
           class="fixed inset-y-0 left-0 z-50 flex flex-col h-screen bg-slate-900 text-white shadow-2xl lg:relative lg:translate-x-0 group border-r border-slate-800 -translate-x-full">

        {{-- Header del Sidebar (Logo centrado y Botón Flotante) --}}
        <div class="relative flex items-center h-20 border-b border-slate-800 shrink-0"
             :class="{
                 'px-5 justify-start': $store.sidebar.open,
                 'px-0 justify-center': !$store.sidebar.open,
                 'transition-all duration-300': ready
             }">
            
            <div class="flex items-center gap-3 overflow-hidden">
                <div class="bg-blue-600 p-2.5 rounded-xl shadow-lg shadow-blue-500/20 shrink-0 flex items-center justify-center">
                    <i class="fa-solid fa-hospital text-white text-lg"></i>
                </div>
                <span x-show="$store.sidebar.open" 
                      x-transition:enter="transition delay-100 duration-200"
                      x-transition:enter-start="opacity-0 translate-x-2"
                      x-transition:enter-end="opacity-100 translate-x-0"
                      class="text-xl font-bold tracking-tight whitespace-nowrap">
                    HOSPITAL <span class="text-blue-500">TG</span>
                </span>
            </div>
            
            {{-- Botón Flotante (escritorio) --}}
            <button @click="$store.sidebar.toggle()" 
                    class="absolute -right-3.5 top-6 hidden lg:flex items-center justify-center h-7 w-7 rounded-full bg-slate-800 border border-slate-700 text-slate-400 hover:text-white hover:bg-slate-700 transition-colors z-50 shadow-md">
                <i class="fa-solid fa-chevron-left text-[10px]" 
                   :class="{ 'rotate-180': !$store.sidebar.open, 'transition-transform duration-300': ready }"></i>
            </button>
        </div>

        {{-- Cuerpo de Navegación con Scroll --}}
        <div class="flex-1 overflow-y-auto overflow-x-hidden py-6 space-y-6 custom-scrollbar">
            
            {{-- Grupo 1: General --}}
            <div>
                <div class="h-6 flex items-center mb-2" :class="$store.sidebar.open ? 'px-6' : 'justify-center'">
                    <p x-show="$store.sidebar.open" class="text-[10px] font-bold uppercase tracking-widest text-slate-500 truncate transition-opacity duration-200">
                        General
                    </p>
                    <div x-show="!$store.sidebar.open" class="w-6 h-px bg-slate-800 transition-opacity duration-200"></div>
                </div>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('home') }}" 
                           class="flex items-center h-12 group {{ request()->routeIs('home') ? 'bg-blue-600 text-white font-semibold shadow-lg shadow-blue-600/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"
                           :class="{
                               'mx-4 px-4 gap-4 justify-start rounded-xl': $store.sidebar.open,
                               'w-12 mx-auto justify-center rounded-xl px-0': !$store.sidebar.open,
                               'transition-all duration-200': ready
                           }" title="Inicio">
                            <div class="w-6 flex justify-center shrink-0">
                                <i class="fa-solid fa-house text-lg group-hover:scale-110 transition-transform"></i>
                            </div>
                            <span x-show="$store.sidebar.open" class="text-sm whitespace-nowrap">Inicio</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('estadisticas.index') }}" 
                           class="flex items-center h-12 group {{ request()->routeIs('estadisticas.index') ? 'bg-blue-600 text-white font-semibold shadow-lg shadow-blue-600/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"
                           :class="{
                               'mx-4 px-4 gap-4 justify-start rounded-xl': $store.sidebar.open,
                               'w-12 mx-auto justify-center rounded-xl px-0': !$store.sidebar.open,
                               'transition-all duration-200': ready
                           }" title="Estadísticas">
                            <div class="w-6 flex justify-center shrink-0">
                                <i class="fa-solid fa-chart-pie text-lg group-hover:scale-110 transition-transform"></i>
                            </div>
                            <span x-show="$store.sidebar.open" class="text-sm whitespace-nowrap">Estadísticas</span>
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Grupo 2: Gestión Médica --}}
            <div>
                <div class="h-6 flex items-center mb-2" :class="$store.sidebar.open ? 'px-6' : 'justify-center'">
                    <p x-show="$store.sidebar.open" class="text-[10px] font-bold uppercase tracking-widest text-slate-500 truncate transition-opacity duration-200">
                        Gestión Médica
                    </p>
                    <div x-show="!$store.sidebar.open" class="w-6 h-px bg-slate-800 transition-opacity duration-200"></div>
                </div>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('pacientes.index') }}" 
                           class="flex items-center h-12 group {{ request()->routeIs('pacientes.index') ? 'bg-blue-600 text-white font-semibold shadow-lg shadow-blue-600/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"
                           :class="{
                               'mx-4 px-4 gap-4 justify-start rounded-xl': $store.sidebar.open,
                               'w-12 mx-auto justify-center rounded-xl px-0': !$store.sidebar.open,
                               'transition-all duration-200': ready
                           }" title="Pacientes">
                            <div class="w-6 flex justify-center shrink-0">
                                <i class="fa-solid fa-user-injured text-lg group-hover:scale-110 transition-transform"></i>
                            </div>
                            <span x-show="$store.sidebar.open" class="text-sm whitespace-nowrap">Pacientes</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('epidemiologia.index') }}" 
                           class="flex items-center h-12 group {{ request()->routeIs('epidemiologia.index') ? 'bg-blue-600 text-white font-semibold shadow-lg shadow-blue-600/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"
                           :class="{
                               'mx-4 px-4 gap-4 justify-start rounded-xl': $store.sidebar.open,
                               'w-12 mx-auto justify-center rounded-xl px-0': !$store.sidebar.open,
                               'transition-all duration-200': ready
                           }" title="Epidemiología">
                            <div class="w-6 flex justify-center shrink-0">
                                <i class="fa-solid fa-shield-virus text-lg group-hover:scale-110 transition-transform"></i>
                            </div>
                            <span x-show="$store.sidebar.open" class="text-sm whitespace-nowrap">Epidemiología</span>
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Grupo 3: Almacén e Inventario --}}
            <div>
                <div class="h-6 flex items-center mb-2" :class="$store.sidebar.open ? 'px-6' : 'justify-center'">
                    <p x-show="$store.sidebar.open" class="text-[10px] font-bold uppercase tracking-widest text-slate-500 truncate transition-opacity duration-200">
                        Almacén e Inventario
                    </p>
                    <div x-show="!$store.sidebar.open" class="w-6 h-px bg-slate-800 transition-opacity duration-200"></div>
                </div>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('almacen.index') }}" 
                           class="flex items-center h-12 group {{ request()->routeIs('almacen.index') ? 'bg-blue-600 text-white font-semibold shadow-lg shadow-blue-600/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"
                           :class="{
                               'mx-4 px-4 gap-4 justify-start rounded-xl': $store.sidebar.open,
                               'w-12 mx-auto justify-center rounded-xl px-0': !$store.sidebar.open,
                               'transition-all duration-200': ready
                           }" title="Almacén">
                            <div class="w-6 flex justify-center shrink-0">
                                <i class="fa-solid fa-box-archive text-lg group-hover:scale-110 transition-transform"></i>
                            </div>
                            <span x-show="$store.sidebar.open" class="text-sm whitespace-nowrap">Almacén</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('medicamentos.index') }}" 
                           class="flex items-center h-12 group {{ request()->routeIs('medicamentos.index') ? 'bg-blue-600 text-white font-semibold shadow-lg shadow-blue-600/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"
                           :class="{
                               'mx-4 px-4 gap-4 justify-start rounded-xl': $store.sidebar.open,
                               'w-12 mx-auto justify-center rounded-xl px-0': !$store.sidebar.open,
                               'transition-all duration-200': ready
                           }" title="Medicamentos">
                            <div class="w-6 flex justify-center shrink-0">
                                <i class="fa-solid fa-pills text-lg group-hover:scale-110 transition-transform"></i>
                            </div>
                            <span x-show="$store.sidebar.open" class="text-sm whitespace-nowrap">Medicamentos</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('retiros.index') }}" 
                           class="flex items-center h-12 group {{ request()->routeIs('retiros.index') ? 'bg-blue-600 text-white font-semibold shadow-lg shadow-blue-600/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"
                           :class="{
                               'mx-4 px-4 gap-4 justify-start rounded-xl': $store.sidebar.open,
                               'w-12 mx-auto justify-center rounded-xl px-0': !$store.sidebar.open,
                               'transition-all duration-200': ready
                           }" title="Retiros">
                            <div class="w-6 flex justify-center shrink-0">
                                <i class="fa-solid fa-hand-holding-medical text-lg group-hover:scale-110 transition-transform"></i>
                            </div>
                            <span x-show="$store.sidebar.open" class="text-sm whitespace-nowrap">Retiros</span>
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Grupo 4: Administración y Seguridad --}}
            <div>
                <div class="h-6 flex items-center mb-2" :class="$store.sidebar.open ? 'px-6' : 'justify-center'">
                    <p x-show="$store.sidebar.open" class="text-[10px] font-bold uppercase tracking-widest text-slate-500 truncate transition-opacity duration-200">
                        Admin y Seguridad
                    </p>
                    <div x-show="!$store.sidebar.open" class="w-6 h-px bg-slate-800 transition-opacity duration-200"></div>
                </div>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('personal.index') }}" 
                           class="flex items-center h-12 group {{ request()->routeIs('personal.index') ? 'bg-blue-600 text-white font-semibold shadow-lg shadow-blue-600/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"
                           :class="{
                               'mx-4 px-4 gap-4 justify-start rounded-xl': $store.sidebar.open,
                               'w-12 mx-auto justify-center rounded-xl px-0': !$store.sidebar.open,
                               'transition-all duration-200': ready
                           }" title="Personal">
                            <div class="w-6 flex justify-center shrink-0">
                                <i class="fa-solid fa-users text-lg group-hover:scale-110 transition-transform"></i>
                            </div>
                            <span x-show="$store.sidebar.open" class="text-sm whitespace-nowrap">Personal</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('personal.bitacora') }}" 
                           class="flex items-center h-12 group {{ request()->routeIs('personal.bitacora') ? 'bg-blue-600 text-white font-semibold shadow-lg shadow-blue-600/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"
                           :class="{
                               'mx-4 px-4 gap-4 justify-start rounded-xl': $store.sidebar.open,
                               'w-12 mx-auto justify-center rounded-xl px-0': !$store.sidebar.open,
                               'transition-all duration-200': ready
                           }" title="Bitácora">
                            <div class="w-6 flex justify-center shrink-0">
                                <i class="fa-solid fa-clock-rotate-left text-lg group-hover:scale-110 transition-transform"></i>
                            </div>
                            <span x-show="$store.sidebar.open" class="text-sm whitespace-nowrap">Bitácora del Sistema</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Footer de Usuario y Cierre de Sesión --}}
        <div class="p-4 border-t border-slate-800 bg-slate-950/30 flex flex-col gap-2 shrink-0">
            
            {{-- Perfil --}}
            <div class="flex items-center"
                 :class="{
                     'px-2 gap-3 justify-start': $store.sidebar.open,
                     'justify-center px-0': !$store.sidebar.open,
                     'transition-all duration-300': ready
                 }">
                <div class="h-10 w-10 rounded-xl bg-blue-600/10 border border-blue-500/20 flex items-center justify-center font-black text-blue-400 tracking-tighter shrink-0 shadow-inner">
                    DC
                </div>
                <div x-show="$store.sidebar.open" class="whitespace-nowrap overflow-hidden">
                    <p class="text-sm font-bold text-slate-200 truncate">David Camacho</p>
                    <p class="text-[11px] text-slate-500 truncate">Administrador</p>
                </div>
            </div>

            {{-- Botón Cerrar Sesión --}}
            {{-- Botón Cerrar Sesión (Convertido a Formulario POST) --}}
<form action="{{ route('logout') }}" method="POST" class="w-full">
    @csrf
    <button type="submit"
            class="flex items-center h-10 group text-red-400 hover:bg-red-500/10 hover:text-red-300 bg-red-400/5 w-full text-left"
            :class="{
                'px-4 gap-3 justify-start mx-2 rounded-xl mt-1 w-[calc(100%-16px)]': $store.sidebar.open,
                'w-10 mx-auto justify-center rounded-xl px-0 mt-2': !$store.sidebar.open,
                'transition-all duration-200': ready
            }" title="Cerrar Sesión">
        <div class="w-6 flex justify-center shrink-0">
            <i class="fa-solid fa-arrow-right-from-bracket text-base group-hover:-translate-x-0.5 group-hover:scale-110 transition-transform"></i>
        </div>
        <span class="font-semibold text-xs whitespace-nowrap" x-show=\"$store.sidebar.open\">Cerrar Sesión</span>
    </button>
</form>
        </div>
    </aside>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 3px;
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