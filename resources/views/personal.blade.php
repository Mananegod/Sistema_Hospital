<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Personal - Hospital TG</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #F7F9FB; }
        .modern-rounded { border-radius: 1rem; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="antialiased text-slate-800 flex min-h-screen" x-data="{ openEdit: false, openView: false, emp: {} }">

    @include('sidebar')

    <main class="flex-1 p-10 overflow-y-auto">
        <div class="max-w-6xl mx-auto">
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Gestión de Personal</h1>
            <p class="text-slate-500 mt-2">Manejo de médicos y equipo técnico del Hospital Dr. Tiburcio Garrido.</p>

            {{-- Bloque de errores para depuración --}}
            @if ($errors->any())
                <div class="mt-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 modern-rounded shadow-sm">
                    <p class="font-bold">Por favor corrige los siguientes errores:</p>
                    <ul class="list-disc ml-5 mt-1 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Mensaje de éxito --}}
            @if(session('success'))
                <div class="mt-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 modern-rounded shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 mt-10">
                <div class="lg:col-span-4">
                    <div class="bg-white p-8 modern-rounded shadow-sm border border-slate-100 sticky top-10">
                        <h2 class="text-lg font-bold mb-6 flex items-center gap-2">
                            <span class="w-2 h-6 bg-blue-600 rounded-full"></span> Nuevo Registro
                        </h2>
                        <form action="{{ route('personal.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="text" name="cedula" value="{{ old('cedula') }}" placeholder="Cédula" required class="w-full bg-slate-50 p-3.5 rounded-xl border-none shadow-inner focus:ring-2 focus:ring-blue-500">
                            
                            <div class="grid grid-cols-2 gap-4">
                                <input type="text" name="nombres" value="{{ old('nombres') }}" placeholder="Nombres" required class="w-full bg-slate-50 p-3.5 rounded-xl border-none shadow-inner focus:ring-2 focus:ring-blue-500">
                                <input type="text" name="apellidos" value="{{ old('apellidos') }}" placeholder="Apellidos" required class="w-full bg-slate-50 p-3.5 rounded-xl border-none shadow-inner focus:ring-2 focus:ring-blue-500">
                            </div>

                            <select name="cargo" required class="w-full bg-slate-50 p-3.5 rounded-xl border-none shadow-inner focus:ring-2 focus:ring-blue-500">
                                <option value="" disabled {{ old('cargo') ? '' : 'selected' }}>Seleccione Cargo</option>
                                <option value="Médico" {{ old('cargo') == 'Médico' ? 'selected' : '' }}>Médico</option>
                                <option value="Enfermero/a" {{ old('cargo') == 'Enfermero/a' ? 'selected' : '' }}>Enfermero/a</option>
                                <option value="Administrativo" {{ old('cargo') == 'Administrativo' ? 'selected' : '' }}>Administrativo</option>
                                <option value="Mantenimiento" {{ old('cargo') == 'Mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
                            </select>

                            <select name="turno" required class="w-full bg-slate-50 p-3.5 rounded-xl border-none shadow-inner focus:ring-2 focus:ring-blue-500">
                                <option value="" disabled {{ old('turno') ? '' : 'selected' }}>Seleccione Turno</option>
                                <option value="Mañana" {{ old('turno') == 'Mañana' ? 'selected' : '' }}>Mañana</option>
                                <option value="Tarde" {{ old('turno') == 'Tarde' ? 'selected' : '' }}>Tarde</option>
                                <option value="Noche" {{ old('turno') == 'Noche' ? 'selected' : '' }}>Noche</option>
                            </select>

                            <input type="text" name="telefono" value="{{ old('telefono') }}" placeholder="Teléfono" required class="w-full bg-slate-50 p-3.5 rounded-xl border-none shadow-inner focus:ring-2 focus:ring-blue-500">
                            
                            <button type="submit" class="w-full bg-slate-900 text-white font-bold py-4 rounded-xl shadow-lg hover:bg-slate-800 transition-all">Registrar Personal</button>
                        </form>
                    </div>
                </div>

                <div class="lg:col-span-8">
                    <div class="bg-white modern-rounded shadow-sm border border-slate-100 overflow-hidden">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 text-slate-400 text-xs font-bold uppercase">
                                <tr>
                                    <th class="px-8 py-5">Nombre y Cargo</th>
                                    <th class="px-6 py-5">Estado</th>
                                    <th class="px-8 py-5 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @forelse($personal as $p)
                                <tr class="{{ !$p->activo ? 'opacity-40 grayscale italic' : '' }} transition-all hover:bg-slate-50/50">
                                    <td class="px-8 py-6">
                                        <p class="font-bold text-slate-900">{{ $p->nombres }} {{ $p->apellidos }}</p>
                                        <p class="text-xs text-blue-600 font-semibold uppercase">{{ $p->cargo }}</p>
                                    </td>
                                    <td class="px-6 py-6">
                                        <span class="px-3 py-1 rounded-lg text-xs font-bold {{ $p->activo ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500' }}">
                                            {{ $p->activo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-6 text-right flex justify-end gap-2">
                                        <button @click="openView = true; emp = {{ json_encode($p) }}" class="p-2 text-slate-400 hover:text-blue-600 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </button>
                                        
                                        @if($p->activo)
                                        <button @click="openEdit = true; emp = {{ json_encode($p) }}" class="p-2 text-slate-400 hover:text-amber-600 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>
                                        @endif

                                        <form action="{{ route('personal.status', $p->id) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="p-2 {{ $p->activo ? 'text-slate-400 hover:text-red-600' : 'text-emerald-600 hover:text-emerald-700' }}">
                                                @if($p->activo)
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                                @else
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                                @endif
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="px-8 py-10 text-center text-slate-400">No hay personal registrado aún.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    {{-- Modal Ver Ficha --}}
    <div x-show="openView" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div class="bg-white w-full max-w-md p-8 rounded-3xl shadow-2xl" @click.away="openView = false">
            <h2 class="text-xl font-bold mb-4 text-slate-900 underline decoration-blue-500">Ficha del Empleado</h2>
            <div class="space-y-3 py-4">
                <p class="text-slate-600"><strong>Cédula:</strong> <span x-text="emp.cedula" class="text-slate-900"></span></p>
                <p class="text-slate-600"><strong>Nombre:</strong> <span x-text="emp.nombres + ' ' + emp.apellidos" class="text-slate-900"></span></p>
                <p class="text-slate-600"><strong>Cargo:</strong> <span x-text="emp.cargo" class="text-blue-600 font-bold"></span></p>
                <p class="text-slate-600"><strong>Turno:</strong> <span x-text="emp.turno" class="text-slate-900"></span></p>
                <p class="text-slate-600"><strong>Teléfono:</strong> <span x-text="emp.telefono" class="text-slate-900"></span></p>
            </div>
            <button @click="openView = false" class="w-full mt-2 bg-slate-900 text-white py-3 rounded-xl font-bold">Cerrar</button>
        </div>
    </div>

    {{-- Modal Editar --}}
    <div x-show="openEdit" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div class="bg-white w-full max-w-md p-8 rounded-3xl shadow-2xl" @click.away="openEdit = false">
            <h2 class="text-xl font-bold mb-6">Editar Información</h2>
            <form :action="'/personal/' + emp.id" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-400 px-1 uppercase">Nombres</label>
                    <input type="text" name="nombres" x-model="emp.nombres" class="w-full bg-slate-50 p-3 rounded-xl border-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-400 px-1 uppercase">Apellidos</label>
                    <input type="text" name="apellidos" x-model="emp.apellidos" class="w-full bg-slate-50 p-3 rounded-xl border-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-400 px-1 uppercase">Cargo</label>
                    <select name="cargo" x-model="emp.cargo" class="w-full bg-slate-50 p-3 rounded-xl border-none focus:ring-2 focus:ring-blue-500">
                        <option value="Médico">Médico</option>
                        <option value="Enfermero/a">Enfermero/a</option>
                        <option value="Administrativo">Administrativo</option>
                        <option value="Mantenimiento">Mantenimiento</option>
                    </select>
                </div>
                <div class="flex gap-2 pt-4">
                    <button type="button" @click="openEdit = false" class="flex-1 bg-slate-100 py-3 rounded-xl font-bold">Cancelar</button>
                    <button type="submit" class="flex-1 bg-blue-600 text-white py-3 rounded-xl font-bold shadow-lg shadow-blue-200">Actualizar</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>