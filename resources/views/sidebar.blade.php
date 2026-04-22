<aside class="w-72 bg-slate-900 text-white flex-shrink-0 sticky top-0 h-screen flex flex-col shadow-2xl">
    <div class="p-8">
        <div class="flex items-center gap-3 mb-10">
            <div class="bg-blue-600 p-2 rounded-xl shadow-lg shadow-blue-500/20">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            </div>
            <span class="text-xl font-bold tracking-tight">HOSPITAL <span class="text-blue-500">TG</span></span>
        </div>
        
        <nav class="space-y-3">
    <a href="{{ route('home') }}" 
       class="flex items-center gap-4 px-4 py-3 rounded-2xl transition-all {{ request()->routeIs('home') ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-400 hover:bg-slate-800' }}">
        <span class="font-semibold">Inicio</span>
    </a>

    <a href="{{ route('medicamentos.index') }}" 
       class="flex items-center gap-4 px-4 py-3 rounded-2xl transition-all {{ request()->routeIs('medicamentos.index') ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-400 hover:bg-slate-800' }}">
        <span class="font-semibold">Inventario</span>
    </a>

    <a href="{{ route('personal.index') }}" 
       class="flex items-center gap-4 px-4 py-3 rounded-2xl transition-all {{ request()->routeIs('personal.index') ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-400 hover:bg-slate-800' }}">
        <span class="font-semibold">Personal</span>
    </a>

    <a href="{{ route('personal.bitacora') }}" 
       class="flex items-center gap-4 px-4 py-3 rounded-2xl transition-all {{ request()->routeIs('personal.bitacora') ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-400 hover:bg-slate-800' }}">
        <span class="font-semibold">Bitácora</span>
    </a>
</nav>
    </div>

    <div class="mt-auto p-8 border-t border-slate-800 bg-slate-900/50">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-full bg-blue-500/20 border border-blue-500/40 flex items-center justify-center text-blue-400 font-bold text-xs">DC</div>
            <div>
                <p class="text-sm font-bold text-white">David Camacho</p>
                <p class="text-xs text-slate-500">Administrador</p>
            </div>
        </div>
        <a href="{{ url('/') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-400 hover:bg-red-500/10 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            <span class="font-semibold text-sm">Cerrar Sesión</span>
        </a>
    </div>
</aside>