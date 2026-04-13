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
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
                <span class="text-xl font-bold tracking-tight">HOSPITAL <span class="text-blue-600">TG</span></span>
            </div>
            <div class="text-sm font-medium text-slate-500 italic">"Salud y Compromiso en Chivacoa"</div>
        </div>
    </nav>

    <header class="hero-gradient text-white py-20 px-8">
        <div class="max-w-7xl mx-auto grid md:grid-cols-2 gap-12 items-center">
            <div>
                <h1 class="text-5xl font-extrabold leading-tight mb-6">
                    Sistema de Gestión <br><span class="text-blue-400">Hospitalaria</span>
                </h1>
                <p class="text-slate-400 text-lg mb-8 max-w-lg">
                    Plataforma integral para el control de insumos médicos y administración interna del Hospital Dr. Tiburcio Garrido.
                </p>
                <div class="flex gap-4">
                    <a href="{{ route('medicamentos.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-4 rounded-xl font-bold transition-all shadow-lg shadow-blue-900/20">
                        Ir al Inventario
                    </a>
                    <button class="border border-slate-700 hover:bg-slate-800 text-white px-8 py-4 rounded-xl font-bold transition-all">
                        Manual de Usuario
                    </button>
                </div>
            </div>
            <div class="hidden md:block relative">
                <div class="absolute inset-0 bg-blue-500/10 blur-3xl rounded-full"></div>
                <div class="bg-slate-800/50 border border-slate-700 p-8 rounded-3xl backdrop-blur-sm relative">
                    <div class="flex gap-2 mb-4">
                        <div class="w-3 h-3 rounded-full bg-red-500"></div>
                        <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                        <div class="w-3 h-3 rounded-full bg-green-500"></div>
                    </div>
                    <p class="font-mono text-blue-300 text-sm italic">// Bienvenido, David Camacho</p>
                    <p class="font-mono text-slate-400 text-sm mt-2">> Estado del Sistema: Óptimo</p>
                    <p class="font-mono text-slate-400 text-sm">> DB: PostgreSQL Conectado</p>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-8 py-16">
        <h2 class="text-2xl font-bold mb-10 text-slate-900">Módulos del Sistema</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <a href="{{ route('medicamentos.index') }}" class="group bg-white p-8 rounded-3xl border border-slate-100 shadow-sm card-hover relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 20 20"><path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"></path></svg>
                </div>
                <div class="bg-blue-50 text-blue-600 w-12 h-12 rounded-2xl flex items-center justify-center mb-6">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
                <h3 class="text-xl font-bold mb-2">Control de Inventario</h3>
                <p class="text-slate-500 text-sm leading-relaxed mb-6">
                    Gestión de stock de medicamentos, control de fechas de vencimiento y asignación por áreas.
                </p>
                <span class="text-blue-600 font-bold text-sm flex items-center gap-2 group-hover:translate-x-2 transition-transform">
                    Acceder ahora 
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </span>
            </a>

            <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm opacity-60">
                <div class="bg-slate-50 text-slate-400 w-12 h-12 rounded-2xl flex items-center justify-center mb-6">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <h3 class="text-xl font-bold mb-2">Registro de Pacientes</h3>
                <p class="text-slate-500 text-sm leading-relaxed mb-6">
                    Módulo para la gestión de historias médicas y control de ingresos (En desarrollo).
                </p>
                <span class="bg-slate-100 text-slate-500 px-3 py-1 rounded-lg text-xs font-bold uppercase">Próximamente</span>
            </div>

            <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm opacity-60">
                <div class="bg-slate-50 text-slate-400 w-12 h-12 rounded-2xl flex items-center justify-center mb-6">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <h3 class="text-xl font-bold mb-2">Reportes Estadísticos</h3>
                <p class="text-slate-500 text-sm leading-relaxed mb-6">
                    Generación de informes mensuales de consumo y necesidades del hospital.
                </p>
                <span class="bg-slate-100 text-slate-500 px-3 py-1 rounded-lg text-xs font-bold uppercase">Próximamente</span>
            </div>

        </div>
    </main>

    <footer class="max-w-7xl mx-auto px-8 py-8 border-t border-slate-100 flex justify-between items-center text-slate-400 text-sm">
        <p>&copy; 2026 Hospital Dr. Tiburcio Garrido.</p>
        <div class="flex gap-6">
            <a href="#" class="hover:text-slate-600 transition-colors">Soporte Técnico</a>
            <a href="#" class="hover:text-slate-600 transition-colors">UPTYAB</a>
        </div>
    </footer>

</body>
</html>