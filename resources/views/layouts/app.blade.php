<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>@yield('title', 'Hospital TG')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
    * {
        font-family: 'Inter', sans-serif;
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
    </style>
    @stack('styles')
</head>

<body class="antialiased text-slate-800">

    <script>
    document.addEventListener('alpine:init', () => {
        // Store para modales normales
        Alpine.store('modal', {
            active: null,
            data: {},
            open(id, data = {}) {
                this.active = id;
                this.data = data;
            },
            close() {
                this.active = null;
                this.data = {};
            },
            isOpen(id) {
                return this.active === id;
            }
        });

        
        Alpine.store('confirm', {
            message: '',
            onConfirm: null,
            open(message, confirmCallback) {
                this.message = message;
                this.onConfirm = confirmCallback;
                Alpine.store('modal').open('globalConfirm');
            },
            confirm() {
                if (this.onConfirm && typeof this.onConfirm === 'function') {
                    this.onConfirm();
                }
                this.close();
            },
            cancel() {
                this.close();
            },
            close() {
                Alpine.store('modal').close();
                this.message = '';
                this.onConfirm = null;
            }
        });

        
        window.confirmAction = (title, message, formIdOrCallback) => {
        
            let callback;
            if (typeof formIdOrCallback === 'string') {
                callback = () => document.getElementById(formIdOrCallback).submit();
            } else if (typeof formIdOrCallback === 'function') {
                callback = formIdOrCallback;
            } else {
                callback = () => {};
            }
            Alpine.store('confirm').open(message, callback);
        };
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @if($showSidebar ?? true)
    <div class="flex min-h-screen">
        @include('sidebar')
        <main class="flex-1 overflow-y-auto p-6 md:p-10">
            @yield('content')
        </main>
    </div>
    @else
    <main class="min-h-screen">
        @yield('content')
    </main>
    @endif

    {{-- Modal de confirmación global --}}
    <x-confirm-modal />

    @stack('modals')
    @stack('scripts')
</body>

</html>