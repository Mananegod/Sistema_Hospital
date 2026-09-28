@extends('layouts.app', ['showSidebar' => false])

@section('title', 'Iniciar Sesión - Hospital TG')

@section('content')

    @include('components.loading-overlay')

    <!-- Contenedor principal con la imagen de fondo actualizada -->
    <div
        class="min-h-screen w-full flex flex-col justify-between bg-cover bg-center bg-no-repeat select-none text-slate-800 antialiased overflow-x-hidden relative"
        style="background-image: url('{{ asset('img/16f97425-a97c-41eb-8ced-13dcf4c1f34c.jfif') }}');">
        
        <!-- Capa superpuesta para mejorar el contraste del cristal -->
        <div class="absolute inset-0 bg-slate-900/30 z-0"></div>

        <main class="flex-1 flex flex-col items-center justify-center p-4 sm:p-10 w-full max-w-6xl mx-auto my-auto relative z-10">

            <div
                class="w-full max-w-70 xs:max-w-xs sm:max-w-md md:max-w-xl lg:max-w-2xl xl:max-w-3xl transition-all duration-300 my-auto">

                <!-- Alerta superior con efecto cristal -->
                <div
                    class="bg-white/60 backdrop-blur-md border-l-4 border-blue-600 rounded-sm p-3.5 sm:p-5 flex gap-3.5 sm:gap-4 shadow-lg mb-5 sm:mb-7 border border-white/30">
                    <i class="fa-solid fa-shield-halved text-blue-700 text-lg sm:text-xl mt-0.5 shrink-0"></i>
                    <div class="text-xs sm:text-sm md:text-base text-slate-800 leading-snug font-medium">
                        <span
                            class="font-bold block text-slate-900 mb-0.5 uppercase tracking-wide text-xs sm:text-sm">Control
                            de Acceso Seguro</span>
                        Asegúrese de ingresar desde un terminal autorizado. Toda actividad es auditada.
                    </div>
                </div>

                <!-- Tarjeta de Login Principal - Glassmorphism -->
                <div class="bg-white/40 backdrop-blur-xl p-6 sm:p-10 md:p-12 border border-white/50 rounded-xl shadow-2xl shadow-black/20 w-full relative transition-all"
                    x-data="{ step: 1, submitting: false }">

                    <div class="mb-6 sm:mb-8 flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-fingerprint text-blue-700 text-2xl sm:text-3xl filter drop-shadow-sm"></i>
                                <h2 class="text-xl sm:text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight drop-shadow-sm"
                                    x-text="step === 1 ? 'Iniciar Sesión' : 'Verificación de seguridad'"></h2>
                            </div>
                            <p class="text-slate-700 text-xs sm:text-sm font-bold uppercase tracking-wider mt-2"
                                x-text="step === 1 ? 'Paso 1: Identificación del personal' : 'Paso 2: Contraseña de acceso'">
                            </p>
                        </div>
                    </div>

                    @if($errors->any())
                        <div
                            class="bg-red-100/90 backdrop-blur-sm border-l-4 border-red-500 text-red-800 p-3.5 sm:p-4 rounded-sm text-xs sm:text-sm font-medium shadow-md mb-6 flex items-center gap-3 border border-red-200/50">
                            <i class="fa-solid fa-circle-xmark text-red-600 text-lg shrink-0"></i>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    @endif

                    <form action="{{ url('/login') }}" method="POST"
                        @submit="submitting = true; $store.loading.activate('Verificando credenciales con el servidor...')">
                        @csrf

                        <div class="grid grid-cols-1 grid-rows-1 items-start">

                            <div x-show="step === 1" x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 translate-x-2"
                                x-transition:enter-end="opacity-100 translate-x-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 translate-x-0"
                                x-transition:leave-end="opacity-0 -translate-x-2"
                                class="col-start-1 row-start-1 space-y-5 sm:space-y-6 w-full">

                                <div>
                                    <label
                                        class="block text-xs sm:text-sm font-bold text-slate-900 uppercase tracking-wider mb-2 drop-shadow-sm">
                                        Usuario / Nombre <span class="text-red-600">*</span>
                                    </label>
                                    <input type="text" name="nombre" id="nombre" x-ref="userInput" autocomplete="username"
                                        value="{{ old('nombre') }}" placeholder="EJ. JUAN.PEREZ" @keydown.enter.prevent="
                                                if ($refs.userInput.value.trim() === '') {
                                                    $refs.userInput.classList.add('border-red-500', 'ring-4', 'ring-red-500/30', 'animate-bounce');
                                                    setTimeout(() => {
                                                        $refs.userInput.classList.remove('border-red-500', 'ring-4', 'ring-red-500/30', 'animate-bounce');
                                                    }, 600);
                                                    return;
                                                }
                                                step = 2;
                                                $nextTick(() => {
                                                    if ($refs.passwordInput) {
                                                        $refs.passwordInput.focus();
                                                    }
                                                });
                                            "
                                        class="w-full bg-white/60 backdrop-blur-sm text-slate-900 placeholder-slate-500 rounded-sm px-4 py-3.5 sm:py-4 text-base sm:text-lg font-semibold shadow-inner outline-none transition border border-white/60 focus:bg-white/90 focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 uppercase">
                                    @error('nombre')
                                        <div
                                            class="text-red-700 text-xs sm:text-sm font-bold mt-2 flex items-center gap-1.5 bg-white/50 backdrop-blur-sm inline-flex px-2 py-1 rounded-sm">
                                            <i class="fa-solid fa-circle-exclamation text-xs"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <button type="button" @click="
                                        if ($refs.userInput.value.trim() === '') {
                                            $refs.userInput.classList.add('border-red-500', 'ring-4', 'ring-red-500/30', 'animate-bounce');
                                            setTimeout(() => {
                                                $refs.userInput.classList.remove('border-red-500', 'ring-4', 'ring-red-500/30', 'animate-bounce');
                                            }, 600);
                                            return;
                                        }
                                        step = 2;
                                        $nextTick(() => {
                                            if ($refs.passwordInput) {
                                                $refs.passwordInput.focus();
                                            }
                                        });
                                    "
                                    class="w-full bg-blue-600/90 hover:bg-blue-700 backdrop-blur-sm text-white font-bold py-3.5 sm:py-4 px-4 rounded-sm shadow-lg shadow-blue-900/30 transition-all active:scale-98 text-sm sm:text-base uppercase tracking-wider flex items-center justify-center gap-3 cursor-pointer border border-blue-500/50">
                                    <span>Siguiente</span>
                                    <i class="fa-solid fa-arrow-right text-sm"></i>
                                </button>
                            </div>

                            <div x-show="step === 2" x-cloak x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 translate-x-2"
                                x-transition:enter-end="opacity-100 translate-x-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 translate-x-0"
                                x-transition:leave-end="opacity-0 -translate-x-2"
                                class="col-start-1 row-start-1 space-y-5 sm:space-y-6 w-full" x-data="{ showPass: false, password: '' }">

                                <div
                                    class="bg-white/60 backdrop-blur-sm border border-white/60 p-3.5 sm:p-4 rounded-sm flex items-center justify-between shadow-sm">
                                    <div class="flex items-center gap-3.5 min-w-0">
                                        <div
                                            class="w-9 h-9 sm:w-10 sm:h-10 bg-blue-600 text-white rounded-sm flex items-center justify-center shrink-0 shadow-sm">
                                            <i class="fa-solid fa-user text-sm"></i>
                                        </div>
                                        <span class="text-slate-900 font-bold truncate text-xs sm:text-sm uppercase">
                                            {{ old('nombre') ?? 'Usuario' }}
                                        </span>
                                    </div>
                                    <button type="button"
                                        @click="step = 1; $nextTick(() => { if ($refs.userInput) $refs.userInput.focus(); })"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white/80 hover:bg-white border border-white text-slate-800 text-xs font-bold rounded-sm transition shadow-sm cursor-pointer active:scale-95">
                                        <i class="fa-solid fa-pen-to-square text-blue-700 text-xs"></i>
                                        <span>Cambiar</span>
                                    </button>
                                </div>

                                <div>
                                    <label
                                        class="block text-xs sm:text-sm font-bold text-slate-900 uppercase tracking-wider mb-2 drop-shadow-sm">
                                        Contraseña <span class="text-red-600">*</span>
                                    </label>
                                    <div class="relative">
                                        <input :type="showPass ? 'text' : 'password'" name="password" id="password"
                                            x-model="password" x-ref="passwordInput" autocomplete="current-password" placeholder="••••••••••••"
                                            class="w-full bg-white/60 backdrop-blur-sm text-slate-900 placeholder-slate-500 rounded-sm px-4 py-3.5 sm:py-4 pr-12 text-base sm:text-lg font-semibold shadow-inner outline-none transition border border-white/60 focus:bg-white/90 focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20">
                                        <button type="button" @click="showPass = !showPass"
                                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-600 hover:text-slate-900 transition cursor-pointer">
                                            <i class="fa-solid text-base" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                                        </button>
                                    </div>
                                    @error('password')
                                        <div
                                            class="text-red-700 text-xs sm:text-sm font-bold mt-2 flex items-center gap-1.5 bg-white/50 backdrop-blur-sm inline-flex px-2 py-1 rounded-sm">
                                            <i class="fa-solid fa-circle-exclamation text-xs"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <button type="submit" :disabled="submitting || password.trim() === ''"
                                    class="w-full bg-blue-600/90 hover:bg-blue-700 backdrop-blur-sm disabled:bg-slate-400/50 disabled:text-slate-200 disabled:border-transparent text-white font-bold py-3.5 sm:py-4 px-4 rounded-sm shadow-lg shadow-blue-900/30 transition-all active:scale-98 text-sm sm:text-base uppercase tracking-wider cursor-pointer disabled:cursor-not-allowed border border-blue-500/50">
                                    Acceder al sistema
                                </button>
                            </div>

                        </div>
                    </form>
                </div>

                <div class="text-center mt-5 sm:mt-7">
                    <span class="text-xs sm:text-sm text-slate-900 font-bold leading-relaxed block drop-shadow-md bg-white/30 backdrop-blur-xs inline-block px-4 py-1.5 rounded-full border border-white/20">
                        ¿Problemas de acceso? Comuníquese con el <span
                            class="text-black uppercase">Administrador del Sistema</span>.
                    </span>
                </div>
            </div>

        </main>

        <!-- Footer adaptado con efecto cristal para mantener consistencia -->
        <footer
            class="w-full text-center py-3.5 sm:py-5 bg-white/30 backdrop-blur-md border-t border-white/20 shrink-0 px-4 relative z-10 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
            <p class="text-[10px] sm:text-xs font-bold text-slate-800 uppercase tracking-widest drop-shadow-sm">
                Chivacoa - Yaracuy
            </p>
        </footer>

    </div>
@endsection

@push('scripts')
    <script>
    </script>
@endpush