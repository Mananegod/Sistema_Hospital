
@extends('layouts.app', ['showSidebar' => false])

@section('title', 'Iniciar Sesión - Hospital TG')

@section('content')
<div class="min-h-screen flex items-center justify-center p-6 bg-gradient-to-br from-slate-900 to-slate-800">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="inline-flex bg-blue-600 p-3 rounded-2xl shadow-xl shadow-blue-500/20 mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-extrabold text-white tracking-tight">HOSPITAL <span class="text-blue-500">TG</span></h1>
            <p class="text-slate-400 mt-2 font-medium">Sistema de Gestión de Inventario</p>
        </div>

        <div class="bg-white/10 backdrop-blur-md p-8 rounded-2xl border border-white/20 shadow-2xl">
            <h2 class="text-2xl font-bold text-white mb-6">Iniciar Sesión</h2>

            <form action="{{ url('/login') }}" method="POST" class="space-y-6">
                @csrf

                @if($errors->any())
                    <div class="bg-red-500/20 border border-red-400 text-red-200 p-4 rounded-xl text-sm font-medium">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-2 ml-1">Usuario</label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" required
                           class="w-full bg-slate-800/50 border border-slate-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-2 ml-1">Contraseña</label>
                    <input type="password" name="password" required
                           class="w-full bg-slate-800/50 border border-slate-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 outline-none transition">
                </div>

                <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-lg shadow-blue-500/20 transition active:scale-95">
                    Acceder al Sistema
                </button>
            </form>
        </div>

        <p class="text-center text-slate-500 text-sm mt-10">
            &copy; 2026 Hospital Dr. Tiburcio Garrido · Chivacoa, Yaracuy
        </p>
    </div>
</div>
@endsection