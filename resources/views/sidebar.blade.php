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
         class="fixed inset-0 bg-gray-900/80 z-40 lg:hidden" 
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
           class="fixed inset-y-0 left-0 z-50 flex flex-col h-screen bg-white text-gray-700 shadow-sm lg:relative lg:translate-x-0 group border-r border-gray-200 -translate-x-full">

        {{-- Header del Sidebar (Logo centrado y Botón Flotante) --}}
        <div class="relative flex items-center h-20 border-b border-gray-200 shrink-0"
             :class="{
                 'px-5 justify-start': $store.sidebar.open,
                 'px-0 justify-center': !$store.sidebar.open,
                 'transition-all duration-300': ready
             }">
            
            <div class="flex items-center gap-3 overflow-hidden">
                <div class="bg-blue-600 p-2.5 rounded-sm shadow-sm shrink-0 flex items-center justify-center">
                    <i class="fa-solid fa-hospital text-white text-lg"></i>
                </div>
                <span x-show="$store.sidebar.open" 
                      x-transition:enter="transition delay-100 duration-200"
                      x-transition:enter-start="opacity-0 translate-x-2"
                      x-transition:enter-end="opacity-100 translate-x-0"
                      class="text-xl font-bold tracking-tight text-gray-900 whitespace-nowrap">
                    HOSPITAL <span class="text-blue-600">TG</span>
                </span>
            </div>
            
            {{-- Botón Flotante (escritorio) --}}
            <button @click="$store.sidebar.toggle()" 
                    class="absolute -right-3.5 top-6 hidden lg:flex items-center justify-center h-7 w-7 rounded-sm bg-white border border-gray-300 text-gray-500 hover:text-gray-800 hover:bg-gray-100 transition-colors z-50 shadow-sm">
                <i class="fa-solid fa-chevron-left text-[10px]" 
                   :class="{ 'rotate-180': !$store.sidebar.open, 'transition-transform duration-300': ready }"></i>
            </button>
        </div>

        {{-- Cuerpo de Navegación con Scroll --}}
        <div class="flex-1 overflow-y-auto overflow-x-hidden py-6 space-y-6 custom-scrollbar">
            
            {{-- Grupo 1: General --}}
            <div>
                <div class="h-6 flex items-center mb-2" :class="$store.sidebar.open ? 'px-6' : 'justify-center'">
                    <p x-show="$store.sidebar.open" class="text-[10px] font-bold uppercase tracking-widest text-gray-400 truncate transition-opacity duration-200">
                        General
                    </p>
                    <div x-show="!$store.sidebar.open" class="w-6 h-px bg-gray-300 transition-opacity duration-200"></div>
                </div>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('home') }}" 
                           class="flex items-center h-12 group sidebar-link {{ request()->routeIs('home') ? 'sidebar-link-active' : '' }}"
                           :class="{
                               'mx-4 px-4 gap-4 justify-start rounded-sm': $store.sidebar.open,
                               'w-12 mx-auto justify-center rounded-sm px-0': !$store.sidebar.open,
                               'transition-all duration-200': ready
                           }" title="Inicio">
                            <div class="w-6 flex justify-center shrink-0">
                                <i class="fa-solid fa-house text-lg"></i>
                            </div>
                            <span x-show="$store.sidebar.open" class="text-sm whitespace-nowrap">Inicio</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('estadisticas.index') }}" 
                           class="flex items-center h-12 group sidebar-link {{ request()->routeIs('estadisticas.index') ? 'sidebar-link-active' : '' }}"
                           :class="{
                               'mx-4 px-4 gap-4 justify-start rounded-sm': $store.sidebar.open,
                               'w-12 mx-auto justify-center rounded-sm px-0': !$store.sidebar.open,
                               'transition-all duration-200': ready
                           }" title="Estadísticas">
                            <div class="w-6 flex justify-center shrink-0">
                                <i class="fa-solid fa-chart-pie text-lg"></i>
                            </div>
                            <span x-show="$store.sidebar.open" class="text-sm whitespace-nowrap">Estadísticas</span>
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Grupo 2: Gestión Médica --}}
            <div>
                <div class="h-6 flex items-center mb-2" :class="$store.sidebar.open ? 'px-6' : 'justify-center'">
                    <p x-show="$store.sidebar.open" class="text-[10px] font-bold uppercase tracking-widest text-gray-400 truncate transition-opacity duration-200">
                        Gestión Médica
                    </p>
                    <div x-show="!$store.sidebar.open" class="w-6 h-px bg-gray-300 transition-opacity duration-200"></div>
                </div>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('pacientes.index') }}" 
                           class="flex items-center h-12 group sidebar-link {{ request()->routeIs('pacientes.index') ? 'sidebar-link-active' : '' }}"
                           :class="{
                               'mx-4 px-4 gap-4 justify-start rounded-sm': $store.sidebar.open,
                               'w-12 mx-auto justify-center rounded-sm px-0': !$store.sidebar.open,
                               'transition-all duration-200': ready
                           }" title="Pacientes">
                            <div class="w-6 flex justify-center shrink-0">
                                <i class="fa-solid fa-user-injured text-lg"></i>
                            </div>
                            <span x-show="$store.sidebar.open" class="text-sm whitespace-nowrap">Pacientes</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('epidemiologia.index') }}" 
                           class="flex items-center h-12 group sidebar-link {{ request()->routeIs('epidemiologia.index') ? 'sidebar-link-active' : '' }}"
                           :class="{
                               'mx-4 px-4 gap-4 justify-start rounded-sm': $store.sidebar.open,
                               'w-12 mx-auto justify-center rounded-sm px-0': !$store.sidebar.open,
                               'transition-all duration-200': ready
                           }" title="Epidemiología">
                            <div class="w-6 flex justify-center shrink-0">
                                <i class="fa-solid fa-shield-virus text-lg"></i>
                            </div>
                            <span x-show="$store.sidebar.open" class="text-sm whitespace-nowrap">Epidemiología</span>
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Grupo 3: Almacén e Inventario --}}
            <div>
                <div class="h-6 flex items-center mb-2" :class="$store.sidebar.open ? 'px-6' : 'justify-center'">
                    <p x-show="$store.sidebar.open" class="text-[10px] font-bold uppercase tracking-widest text-gray-400 truncate transition-opacity duration-200">
                        Almacén e Inventario
                    </p>
                    <div x-show="!$store.sidebar.open" class="w-6 h-px bg-gray-300 transition-opacity duration-200"></div>
                </div>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('almacen.index') }}" 
                           class="flex items-center h-12 group sidebar-link {{ request()->routeIs('almacen.index') ? 'sidebar-link-active' : '' }}"
                           :class="{
                               'mx-4 px-4 gap-4 justify-start rounded-sm': $store.sidebar.open,
                               'w-12 mx-auto justify-center rounded-sm px-0': !$store.sidebar.open,
                               'transition-all duration-200': ready
                           }" title="Almacén">
                            <div class="w-6 flex justify-center shrink-0">
                                <i class="fa-solid fa-box-archive text-lg"></i>
                            </div>
                            <span x-show="$store.sidebar.open" class="text-sm whitespace-nowrap">Almacén</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('medicamentos.index') }}" 
                           class="flex items-center h-12 group sidebar-link {{ request()->routeIs('medicamentos.index') ? 'sidebar-link-active' : '' }}"
                           :class="{
                               'mx-4 px-4 gap-4 justify-start rounded-sm': $store.sidebar.open,
                               'w-12 mx-auto justify-center rounded-sm px-0': !$store.sidebar.open,
                               'transition-all duration-200': ready
                           }" title="Medicamentos">
                            <div class="w-6 flex justify-center shrink-0">
                                <i class="fa-solid fa-pills text-lg"></i>
                            </div>
                            <span x-show="$store.sidebar.open" class="text-sm whitespace-nowrap">Medicamentos</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('retiros.index') }}" 
                           class="flex items-center h-12 group sidebar-link {{ request()->routeIs('retiros.index') ? 'sidebar-link-active' : '' }}"
                           :class="{
                               'mx-4 px-4 gap-4 justify-start rounded-sm': $store.sidebar.open,
                               'w-12 mx-auto justify-center rounded-sm px-0': !$store.sidebar.open,
                               'transition-all duration-200': ready
                           }" title="Retiros">
                            <div class="w-6 flex justify-center shrink-0">
                                <i class="fa-solid fa-hand-holding-medical text-lg"></i>
                            </div>
                            <span x-show="$store.sidebar.open" class="text-sm whitespace-nowrap">Retiros</span>
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Grupo 4: Administración y Seguridad --}}
            <div>
                <div class="h-6 flex items-center mb-2" :class="$store.sidebar.open ? 'px-6' : 'justify-center'">
                    <p x-show="$store.sidebar.open" class="text-[10px] font-bold uppercase tracking-widest text-gray-400 truncate transition-opacity duration-200">
                        Admin y Seguridad
                    </p>
                    <div x-show="!$store.sidebar.open" class="w-6 h-px bg-gray-300 transition-opacity duration-200"></div>
                </div>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('personal.index') }}" 
                           class="flex items-center h-12 group sidebar-link {{ request()->routeIs('personal.index') ? 'sidebar-link-active' : '' }}"
                           :class="{
                               'mx-4 px-4 gap-4 justify-start rounded-sm': $store.sidebar.open,
                               'w-12 mx-auto justify-center rounded-sm px-0': !$store.sidebar.open,
                               'transition-all duration-200': ready
                           }" title="Personal">
                            <div class="w-6 flex justify-center shrink-0">
                                <i class="fa-solid fa-users text-lg"></i>
                            </div>
                            <span x-show="$store.sidebar.open" class="text-sm whitespace-nowrap">Personal</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('personal.bitacora') }}" 
                           class="flex items-center h-12 group sidebar-link {{ request()->routeIs('personal.bitacora') ? 'sidebar-link-active' : '' }}"
                           :class="{
                               'mx-4 px-4 gap-4 justify-start rounded-sm': $store.sidebar.open,
                               'w-12 mx-auto justify-center rounded-sm px-0': !$store.sidebar.open,
                               'transition-all duration-200': ready
                           }" title="Bitácora">
                            <div class="w-6 flex justify-center shrink-0">
                                <i class="fa-solid fa-clock-rotate-left text-lg"></i>
                            </div>
                            <span x-show="$store.sidebar.open" class="text-sm whitespace-nowrap">Bitácora del Sistema</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Footer de Usuario y Cierre de Sesión --}}
        <div class="p-4 border-t border-gray-200 bg-gray-50/80 flex flex-col gap-2 shrink-0">
            
            {{-- Perfil --}}
            <div class="flex items-center"
                 :class="{
                     'px-2 gap-3 justify-start': $store.sidebar.open,
                     'justify-center px-0': !$store.sidebar.open,
                     'transition-all duration-300': ready
                 }">
                <div class="h-10 w-10 rounded-sm bg-blue-50 border border-blue-200 flex items-center justify-center font-black text-blue-600 tracking-tighter shrink-0">
                    DC
                </div>
                <div x-show="$store.sidebar.open" class="whitespace-nowrap overflow-hidden">
                    <p class="text-sm font-bold text-gray-800 truncate">David Camacho</p>
                    <p class="text-[11px] text-gray-500 truncate">Administrador</p>
                </div>
            </div>

            {{-- Botón Cerrar Sesión (Formulario POST) --}}
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"
                        class="flex items-center h-10 group text-red-600 hover:bg-red-100 hover:text-red-700 bg-red-100 font-semibold"
                        :class="{
                            'px-4 gap-3 justify-start mx-2 rounded-sm mt-1 w-[calc(100%-16px)]': $store.sidebar.open,
                            'w-10 mx-auto justify-center rounded-sm px-0 mt-2': !$store.sidebar.open,
                            'transition-all duration-200': ready
                        }" title="Cerrar Sesión">
                    <div class="w-6 flex justify-center shrink-0">
                        <i class="fa-solid fa-arrow-right-from-bracket text-base"></i>
                    </div>
                    <span class="text-xs whitespace-nowrap" 
                          x-show="$store.sidebar.open"
                          x-transition:enter="transition ease-out duration-200 delay-100"
                          x-transition:enter-start="opacity-0 -translate-x-2"
                          x-transition:enter-end="opacity-100 translate-x-0">Cerrar Sesión</span>
                </button>
            </form>
        </div>
    </aside>
</div>

<style>
    /* ==============================================
       VARIABLES Y ESTILOS DEL SIDEBAR
       ============================================== */
    :root {
        /* ---Fondo de los botones inactivos--- */
        --sidebar-link-bg: #edeff1ec;  /*el bg de los botone*/  

        --sidebar-link-border: none;    /* el borde */

        --sidebar-link-text: #3a3e44;      /*el texto, osea, el color del texto*/
        
        --sidebar-link-hover-bg: #f3f4f6;   /* el hover del texto*/ 

        /* ---Colores para el botón activo--- */
        --sidebar-link-active-bg: #2564ebe7;   
        --sidebar-link-active-text: #ffffff; 
        --sidebar-link-active-border: #2563eb;
        --sidebar-link-active-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05); 
    }

    /* Estilo base de los enlaces */
    .sidebar-link {
        background-color: var(--sidebar-link-bg);
        border: 1px solid var(--sidebar-link-border);
        color: var(--sidebar-link-text);
    }
    .sidebar-link:hover {
        background-color: var(--sidebar-link-hover-bg);
    }

    /* Estilo para el enlace activo (mayor especificidad) */
    .sidebar-link.sidebar-link-active {
        background-color: var(--sidebar-link-active-bg);
        color: var(--sidebar-link-active-text);
        border-color: var(--sidebar-link-active-border);
        font-weight: 600;
        box-shadow: var(--sidebar-link-active-shadow);
    }

    /* Ajustes del scrollbar */
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 9999px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #9ca3af;
    }
</style>