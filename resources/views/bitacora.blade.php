<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bitácora de Auditoría - Hospital TG</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #F7F9FB; }
        .modern-rounded { border-radius: 1rem; }
    </style>
</head>
<body class="antialiased text-slate-800 flex min-h-screen">

    @include('sidebar')

    <main class="flex-1 p-10 overflow-y-auto">
        <div class="max-w-6xl mx-auto">
            <div class="flex justify-between items-end mb-10">
                <div>
                    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight italic underline decoration-blue-500 decoration-4 underline-offset-8">Bitácora Global de Auditoría</h1>
                    <p class="text-slate-500 mt-4 font-medium">Historial centralizado de movimientos en todos los módulos del sistema.</p>
                </div>
                <div class="bg-blue-50 border border-blue-100 px-4 py-2 rounded-xl">
                    <span class="text-blue-700 text-xs font-bold uppercase tracking-wider">Control de Calidad Hospitalaria</span>
                </div>
            </div>

            <div class="bg-white modern-rounded shadow-sm border border-slate-100 overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-slate-900 text-slate-400 text-[10px] font-bold uppercase tracking-widest">
                        <tr>
                            <th class="px-8 py-5">Módulo</th>
                            <th class="px-6 py-5">Acción</th>
                            <th class="px-6 py-5">Descripción del Evento</th>
                            <th class="px-8 py-5 text-right">Fecha y Hora</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($registros as $reg)
                        <tr class="hover:bg-blue-50/30 transition-colors">
                            <td class="px-8 py-6">
                                <span class="px-3 py-1 rounded-lg text-[10px] font-black uppercase shadow-sm border 
                                    {{ $reg->modulo == 'Personal' ? 'bg-purple-50 text-purple-700 border-purple-100' : 'bg-amber-50 text-amber-700 border-amber-100' }}">
                                    {{ $reg->modulo }}
                                </span>
                            </td>
                            <td class="px-6 py-6 font-bold text-sm">
                                @if(str_contains(strtolower($reg->accion), 'elimin'))
                                    <span class="text-red-600 flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                        {{ $reg->accion }}
                                    </span>
                                @else
                                    <span class="text-emerald-600 flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                        {{ $reg->accion }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-6 text-slate-600 text-sm font-medium leading-relaxed">
                                {{ $reg->descripcion }}
                                <p class="text-[10px] text-slate-400 mt-1 uppercase tracking-tighter">Realizado por: {{ $reg->usuario }}</p>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <p class="text-sm font-bold text-slate-700">{{ $reg->created_at->format('d/m/Y') }}</p>
                                <p class="text-[10px] text-slate-400 font-mono italic">{{ $reg->created_at->format('h:i A') }}</p>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-8 py-20 text-center">
                                <div class="flex flex-col items-center opacity-20">
                                    <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    <p class="font-bold text-xl uppercase tracking-widest">Sin registros de actividad</p>
                                    <p class="text-sm">La bitácora está limpia por ahora.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-8 p-6 bg-slate-900 rounded-2xl text-white flex items-center justify-between shadow-xl">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-blue-600 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold">Respaldo Inmutable Hospital TG</p>
                        <p class="text-[10px] text-slate-400 uppercase tracking-widest">Los registros no pueden ser eliminados permanentemente por políticas de seguridad.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>