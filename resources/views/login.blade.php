<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Hospital Dr. Tiburcio Garrido</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        body { 
            font-family: 'Inter', sans-serif; 
            background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
        }
        .modern-card { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6">

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

        <div class="modern-card p-10 rounded-[2rem] border border-slate-700/50 shadow-2xl">
            <h2 class="text-xl font-bold text-white mb-8">Iniciar Sesión</h2>

            <form action="{{ url('/login') }}" method="POST" class="space-y-6">
                @csrf
                
                @if($errors->any())
                    <div class="bg-red-500/10 border border-red-500/50 text-red-400 p-4 rounded-xl text-sm font-medium">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2 ml-1">Nombre de Usuario</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </span>
                        <input type="text" name="nombre" required 
                            class="w-full bg-slate-800/50 border border-slate-700 text-white rounded-xl pl-12 pr-4 py-4 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all placeholder:text-slate-600" 
                            placeholder="Ej: David Camacho">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2 ml-1">Contraseña</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </span>
                        <input type="password" name="password" required 
                            class="w-full bg-slate-800/50 border border-slate-700 text-white rounded-xl pl-12 pr-4 py-4 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all placeholder:text-slate-600" 
                            placeholder="••••••••">
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-500/20 transition-all active:scale-[0.98]">
                        Acceder al Sistema
                    </button>
                </div>
            </form>
        </div>

        <p class="text-center text-slate-500 text-sm mt-10">
            &copy; 2026 Hospital Dr. Tiburcio Garrido <br> 
            <span class="text-xs uppercase tracking-widest opacity-50">Desarrollo Interno</span>
        </p>
    </div>

</body>
</html>

