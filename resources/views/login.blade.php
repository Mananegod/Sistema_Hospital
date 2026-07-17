@extends('layouts.app', ['showSidebar' => false])

@section('title', 'Iniciar Sesión - Hospital TG')

@section('content')

    @include('components.loading-overlay')

    <div
        class="min-h-screen w-full flex flex-col justify-between bg-linear-to-br from-slate-100 via-[#F3F7FC] to-blue-50/40 select-none text-slate-700 antialiased overflow-x-hidden">

        <main class="flex-1 flex flex-col items-center justify-center p-4 sm:p-10 w-full max-w-6xl mx-auto my-auto">

            <div
                class="w-full max-w-70 xs:max-w-xs sm:max-w-md md:max-w-xl lg:max-w-2xl xl:max-w-3xl transition-all duration-300 my-auto">

                <div
                    class="bg-white/90 backdrop-blur-xs border-l-4 border-blue-600 rounded-sm p-3.5 sm:p-5 flex gap-3.5 sm:gap-4 shadow-sm mb-5 sm:mb-7 border ">
                    <i class="fa-solid fa-shield-halved text-blue-600 text-lg sm:text-xl mt-0.5 shrink-0"></i>
                    <div class="text-xs sm:text-sm md:text-base text-slate-700 leading-snug font-medium">
                        <span
                            class="font-bold block text-slate-900 mb-0.5 uppercase tracking-wide text-xs sm:text-sm">Control
                            de Acceso Seguro</span>
                        Asegúrese de ingresar desde un terminal autorizado. Toda actividad es auditada.
                    </div>
                </div>

                <div class="bg-white/95 backdrop-blur-xl p-6 sm:p-10 md:p-12 border border-slate-200/90 rounded-sm shadow-md shadow-blue-900/15 w-full relative transition-all"
                    x-data="{ step: 1, submitting: false }">

                    <div class="mb-6 sm:mb-8 flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-fingerprint text-blue-600 text-2xl sm:text-3xl"></i>
                                <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-slate-700 tracking-tight"
                                    x-text="step === 1 ? 'Iniciar Sesión' : 'Verificación de seguridad'"></h2>
                            </div>
                            <p class="text-slate-500 text-xs sm:text-sm font-semibold uppercase tracking-wider mt-2"
                                x-text="step === 1 ? 'Paso 1: Identificación del personal' : 'Paso 2: Contraseña de acceso'">
                            </p>
                        </div>
                    </div>

                    @if($errors->any())
                        <div
                            class="bg-red-50 border-l-4 border-red-500 text-red-700 p-3.5 sm:p-4 rounded-sm text-xs sm:text-sm font-medium shadow-2xs mb-6 flex items-center gap-3">
                            <i class="fa-solid fa-circle-xmark text-red-500 text-lg shrink-0"></i>
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
                                        class="block text-xs sm:text-sm font-bold text-slate-700 uppercase tracking-wider mb-2">
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
                                        class="w-full bg-slate-50 text-slate-700 rounded-sm px-4 py-3.5 sm:py-4 text-base sm:text-lg font-semibold shadow-inner outline-none transition border border-slate-300 focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 uppercase">
                                    @error('nombre')
                                        <div
                                            class="text-red-600 text-xs sm:text-sm font-semibold mt-2 flex items-center gap-1.5">
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
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 sm:py-4 px-4 rounded-sm shadow-md shadow-blue-600/25 transition-all active:scale-98 text-sm sm:text-base uppercase tracking-wider flex items-center justify-center gap-3 cursor-pointer">
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
                                class="col-start-1 row-start-1 space-y-5 sm:space-y-6 w-full" x-data="{ showPass: false }">

                                <div
                                    class="bg-slate-50 border border-slate-200 p-3.5 sm:p-4 rounded-sm flex items-center justify-between shadow-2xs">
                                    <div class="flex items-center gap-3.5 min-w-0">
                                        <div
                                            class="w-9 h-9 sm:w-10 sm:h-10 bg-blue-100 text-blue-600 rounded-sm flex items-center justify-center shrink-0">
                                            <i class="fa-solid fa-user text-sm"></i>
                                        </div>
                                        <span class="text-slate-700 font-bold truncate text-xs sm:text-sm uppercase">
                                            {{ old('nombre') ?? 'Usuario' }}
                                        </span>
                                    </div>
                                    <button type="button"
                                        @click="step = 1; $nextTick(() => { if ($refs.userInput) $refs.userInput.focus(); })"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white hover:bg-slate-100 border border-slate-300 text-slate-700 text-xs font-semibold rounded-sm transition shadow-2xs cursor-pointer active:scale-95">
                                        <i class="fa-solid fa-pen-to-square text-blue-600 text-xs"></i>
                                        <span>Cambiar</span>
                                    </button>
                                </div>

                                <div>
                                    <label
                                        class="block text-xs sm:text-sm font-bold text-slate-700 uppercase tracking-wider mb-2">
                                        Contraseña <span class="text-red-600">*</span>
                                    </label>
                                    <div class="relative">
                                        <input :type="showPass ? 'text' : 'password'" name="password" id="password"
                                            x-ref="passwordInput" autocomplete="current-password" placeholder="••••••••••••"
                                            class="w-full bg-slate-50 text-slate-700 rounded-sm px-4 py-3.5 sm:py-4 pr-12 text-base sm:text-lg font-semibold shadow-inner outline-none transition border border-slate-300 focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20">
                                        <button type="button" @click="showPass = !showPass"
                                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-700 transition cursor-pointer">
                                            <i class="fa-solid text-base" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                                        </button>
                                    </div>
                                    @error('password')
                                        <div
                                            class="text-red-600 text-xs sm:text-sm font-semibold mt-2 flex items-center gap-1.5">
                                            <i class="fa-solid fa-circle-exclamation text-xs"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <button type="submit" :disabled="submitting"
                                    class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-slate-200 disabled:text-slate-400 disabled:shadow-none text-white font-bold py-3.5 sm:py-4 px-4 rounded-sm shadow-md shadow-blue-600/25 transition-all active:scale-98 text-sm sm:text-base uppercase tracking-wider cursor-pointer disabled:cursor-not-allowed">
                                    Acceder al sistema
                                </button>
                            </div>

                        </div>
                    </form>
                </div>

                <div class="text-center mt-5 sm:mt-7">
                    <span class="text-xs sm:text-sm text-slate-500 font-medium leading-relaxed block">
                        ¿Problemas de acceso? Comuníquese con el <span
                            class="text-slate-700 font-bold uppercase">Administrador del Sistema</span>.
                    </span>
                </div>
            </div>

        </main>

        <footer
            class="w-full text-center py-3.5 sm:py-5 bg-white/60 backdrop-blur-xs border-t border-slate-200/60 shrink-0 px-4">
            <p class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-widest">
                Chivacoa - Yaracuy
            </p>
        </footer>

    </div>
@endsection

@push('scripts')
    <script>
    </script>
@endpush