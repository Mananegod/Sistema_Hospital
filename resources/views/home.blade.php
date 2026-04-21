<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Dr. Tiburcio Garrido - Inicio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #F8FAFC; }
        .hero-gradient { background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%); }
        .card-hover:hover { transform: translateY(-5px); transition: all 0.3s ease; }
    </style>
</head>
<body class="antialiased text-slate-800">

    <nav class="bg-white/80 backdrop-blur-md border-b border-slate-100 sticky top-0 z-50 px-8 py-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="bg-blue-600 p-1.5 rounded-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <span class="text-xl font-bold tracking-tight text-slate-900">Hospital <span class="text-blue-600">TG</span></span>
            </div>
            
            <div class="flex items-center gap-6">
                <div class="text-right hidden md:block">
                    <p class="text-sm font-bold text-slate-900">David Camacho</p>
                    <p class="text-xs text-slate-500">Ingeniero de Sistemas</p>
                </div>
                <a href="{{ url('/') }}" class="bg-slate-900 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-800 transition-colors">
                    Cerrar Sesión
                </a>
            </div>
        </div>
    </nav>

    <header class="hero-gradient pt-20 pb-32 px-8">
        <div class="max-w-7xl mx-auto text-center">
            <span class="inline-block px-4 py-1.5 bg-blue-500/10 border border-blue-500/20 rounded-full text-blue-400 text-xs font-bold uppercase tracking-widest mb-6">
                Panel de Administración v1.0
            </span>
            <h1 class="text-4xl md:text-5xl font-extrabold text-white mb-6 leading-tight">
                Sistema Integral de Gestión <br> <span class="text-blue-500">Hospital Dr. Tiburcio Garrido</span>
            </h1>
            <p class="text-slate-400 max-w-2xl mx-auto text-lg leading-relaxed">
                Bienvenido al panel central. Seleccione el módulo correspondiente para gestionar los recursos, personal y estadísticas de la institución.
            </p>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-8 -mt-16 mb-20">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            
            <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm card-hover flex flex-col">
                <div class="bg-blue-50 text-blue-600 w-12 h-12 rounded-2xl flex items-center justify-center mb-6">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-2">Gestión de Inventario</h3>
                <p class="text-slate-500 text-sm leading-relaxed mb-6 flex-grow">
                    Control total de medicamentos, insumos médicos y stock por áreas (Urgencias, UCI, Pediatría).
                </p>
                <a href="{{ route('medicamentos.index') }}" class="inline-flex items-center text-blue-600 font-semibold gap-2 group">
                    Entrar al módulo
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                    </svg>
                </a>
            </div>

            <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm opacity-60 flex flex-col relative overflow-hidden">
                <div class="bg-emerald-50 text-emerald-600 w-12 h-12 rounded-2xl flex items-center justify-center mb-6">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-2">Registro de Pacientes</h3>
                <p class="text-slate-500 text-sm leading-relaxed mb-6 flex-grow">
                    Historiales clínicos digitales, ingresos y gestión de camas por departamento médico.
                </p>
                <span class="bg-emerald-100 text-emerald-600 px-3 py-1 rounded-lg text-xs font-bold w-fit uppercase">Próximamente</span>
            </div>

            <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm opacity-60 flex flex-col relative overflow-hidden">
                <div class="bg-amber-50 text-amber-600 w-12 h-12 rounded-2xl flex items-center justify-center mb-6">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-2">Reportes Estadísticos</h3>
                <p class="text-slate-500 text-sm leading-relaxed mb-6 flex-grow">
                    Generación de informes mensuales de consumo y necesidades críticas del hospital.
                </p>
                <span class="bg-amber-100 text-amber-600 px-3 py-1 rounded-lg text-xs font-bold w-fit uppercase">Próximamente</span>
            </div>

        </div>
    </main>

    <footer class="text-center py-12 border-t border-slate-100">
        <p class="text-slate-400 text-sm">
            &copy; 2026 Hospital Dr. Tiburcio Garrido | Chivacoa, Yaracuy. <br>
        </p>
    </footer>

</body>
</html>