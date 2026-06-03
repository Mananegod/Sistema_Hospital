<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content=\"width=device-width, initial-scale=1.0, viewport-fit=cover\">
    <title>@yield('title', 'Hospital TG')</title>
    
    {{-- CSS Locales e Inmunes a fallos de red --}}
    <script src="{{ asset('js/tailwindcss.js') }}"></script>
    <link rel="icon" type="image/png" href="{{ asset('img/logo-prueba.png') }}">
    <link rel="stylesheet" href="{{ asset('css/font-awesome/all.min.css') }}" />

    <style>
        /* Usamos fuentes del sistema para garantizar el funcionamiento 100% offline sin demoras */
        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        body {
            background-color: #F7F9FB;
        }

        .modern-rounded {
            border-radius: 1rem;
        }

        .input-shadow {
            box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        }

        [x-cloak] {
            display: none !important;
        }

        aside {
            min-width: 5rem !important;
        }
        aside.w-64 {
            min-width: 16rem !important;
        }
    </style>

    {{-- Alpine.js cargado localmente con DEFER para mantener el comportamiento exacto del archivo Online --}}
    <script defer src="{{ asset('js/alpine.min.js') }}"></script>

    <script>
        // Inicialización de las tiendas globales de Alpine (Igual a tu versión online exitosa)
        document.addEventListener('alpine:init', () => {
            Alpine.store('sidebar', {
                open: true,
                mobileOpen: false,
                toggle() { this.open = !this.open },
                toggleMobile() { this.mobileOpen = !this.mobileOpen }
            });

            Alpine.store('modal', {
                current: null,
                data: {},
                open(id, data = {}) {
                    this.current = id;
                    this.data = data;
                },
                close() {
                    this.current = null;
                    this.data = {};
                }
            });

            Alpine.store('toast', {
                messages: [],
                add(text, type = 'success') {
                    const id = Date.now();
                    this.messages.push({ id, text, type });
                    setTimeout(() => this.remove(id), 4000);
                },
                remove(id) {
                    this.messages = this.messages.filter(m => m.id !== id);
                }
            });

            Alpine.store('loading', {
                active: false,
                message: 'Procesando...',
                activate(msg = 'Procesando...') {
                    this.message = msg;
                    this.active = true;
                },
                deactivate() {
                    this.active = false;
                },
                submitForm(form) {
                    this.activate('Cargando datos...');
                    form.submit();
                }
            });
        });
    </script>
</head>

<body>

    @if($showSidebar ?? true)
    <div class="flex h-screen overflow-hidden w-full" x-data>
        
        @include('sidebar')

        <div class="flex flex-col flex-1 min-w-0 overflow-hidden relative">
            
            <header class="lg:hidden flex items-center justify-between p-4 bg-slate-900 text-white shadow-md z-30 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="bg-blue-600 p-2 rounded-xl shadow-lg shadow-blue-500/20 shrink-0">
                        <i class=\"fa-solid fa-hospital text-white\"></i>
                    </div>
                    <span class="text-xl font-bold tracking-tight">HOSPITAL <span class="text-blue-500">TG</span></span>
                </div>
                <button @click="$store.sidebar.toggleMobile()" class="p-2 text-slate-400 hover:text-white rounded-lg focus:outline-none">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
            </header>

            <main class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-10 w-full relative bg-[#F7F9FB]">
                @yield('content')
            </main>
        </div>
    </div>
    @else
    <main class="min-h-screen">
        @yield('content')
    </main>
    @endif

    {{-- Componentes globales de la interfaz --}}
    @include('components.toast-notifications')

</body>
</html>