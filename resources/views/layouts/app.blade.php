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
           
            Alpine.store('sidebar', {
                isExpanded: localStorage.getItem('sidebarExpanded') !== 'false',
                isMobileOpen: false,
                toggleDesktop() {
                    this.isExpanded = !this.isExpanded;
                    localStorage.setItem('sidebarExpanded', this.isExpanded);
                },
                toggleMobile() {
                    this.isMobileOpen = !this.isMobileOpen;
                },
                closeMobile() {
                    this.isMobileOpen = false;
                }
            });

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

            Alpine.store('toast', {
                messages: [],
                add(message, type = 'success') {
                    if (!message || message.trim() === '') return;
                    const id = Date.now();
                    this.messages.push({
                        id,
                        message,
                        type
                    });
                    setTimeout(() => {
                        this.remove(id);
                    }, 3000);
                },
                remove(id) {
                    this.messages = this.messages.filter(m => m.id !== id);
                }
            });

            // ---------- NUEVO STORE: LOADING (con timeout y reintento) ----------
            
            Alpine.store('loading', {
                active: false,        // si el modal está visible
                timedOut: false,      // si se alcanzó el timeout
                retryCallback: null,  // función a ejecutar al reintentar
                timeoutId: null,
                timeoutDuration: 30000, // 30 segundos
                message: 'Procesando solicitud...',
                
                // Inicia la carga y muestra el modal
                start(callback, options = {}) {
                    if (this.active) return; // ya hay una carga activa
                    
                    this.active = true;
                    this.timedOut = false;
                    this.retryCallback = callback || null;
                    this.message = options.message || 'Procesando solicitud...';
                    
                    // Limpiar timeout anterior
                    if (this.timeoutId) clearTimeout(this.timeoutId);
                    
                    // Iniciar timeout
                    this.timeoutId = setTimeout(() => {
                        this.handleTimeout();
                    }, this.timeoutDuration);
                },
                
                // Finaliza la carga correctamente
                stop() {
                    if (this.timeoutId) {
                        clearTimeout(this.timeoutId);
                        this.timeoutId = null;
                    }
                    this.active = false;
                    this.timedOut = false;
                    this.retryCallback = null;
                },
                
                // Maneja el timeout: muestra el mensaje de error y botón reintentar
                handleTimeout() {
                    this.timedOut = true;
                    this.message = 'La solicitud ha demorado demasiado. ¿Deseas reintentar?';
                    // No se cierra el modal, solo cambia el contenido
                },
                
                // Reintentar: ejecuta el callback guardado
                retry() {
                    if (this.retryCallback && typeof this.retryCallback === 'function') {
                        // Limpiar timeout viejo
                        if (this.timeoutId) clearTimeout(this.timeoutId);
                        this.timedOut = false;
                        this.message = 'Reintentando...';
                        // Iniciar nuevo timeout
                        this.timeoutId = setTimeout(() => {
                            this.handleTimeout();
                        }, this.timeoutDuration);
                        // Ejecutar el callback (por lo general el envío del formulario)
                        this.retryCallback();
                    }
                },
                
                // Método específico para formularios estándar
                submitForm(formElement) {
                    if (!formElement || formElement.tagName !== 'FORM') return;
                    
                    // Evita múltiples envíos si ya hay una carga activa
                    if (this.active) return;
                    
                    // Deshabilitar todos los botones de submit dentro del formulario
                    const buttons = formElement.querySelectorAll('button[type="submit"], input[type="submit"]');
                    buttons.forEach(btn => {
                        btn.disabled = true;
                        btn.setAttribute('data-original-text', btn.innerText);
                        btn.innerText = 'Enviando...';
                    });
                    
                    // Guardar referencia al formulario y al evento original (opcional)
                    const submitCallback = () => {
                      
                        formElement.submit();
                    };
                    
                   
                    this.start(submitCallback, { message: 'Enviando datos al servidor...' });
                  
                    submitCallback();
                    
                   
                    if (this.timeoutId) {
                        const restoreButtons = () => {
                            buttons.forEach(btn => {
                                btn.disabled = false;
                                if (btn.getAttribute('data-original-text')) {
                                    btn.innerText = btn.getAttribute('data-original-text');
                                }
                            });
                        };
                        // Si ocurre timeout, restauramos botones
                        const originalHandleTimeout = this.handleTimeout.bind(this);
                        this.handleTimeout = () => {
                            restoreButtons();
                            originalHandleTimeout();
                        };
                    }
                },
                
                // Método para envolver promesas AJAX (fetch, axios, etc.)
                async wrap(promise, options = {}) {
                    if (this.active) return promise;
                    
                    let callbackResolve = null;
                    const wrappedPromise = new Promise((resolve, reject) => {
                        callbackResolve = { resolve, reject };
                    });
                    
                    const retryable = () => {
                      
                        promise()
                            .then(result => {
                                this.stop();
                                callbackResolve.resolve(result);
                            })
                            .catch(err => {
                                this.stop();
                                callbackResolve.reject(err);
                            });
                    };
                    
                    this.start(retryable, options);
                    
                    
                    promise()
                        .then(result => {
                            if (this.active && !this.timedOut) {
                                this.stop();
                                callbackResolve.resolve(result);
                            } else if (!this.active) {
                                callbackResolve.resolve(result);
                            }
                        })
                        .catch(err => {
                            if (this.active && !this.timedOut) {
                                this.stop();
                                callbackResolve.reject(err);
                            } else if (!this.active) {
                                callbackResolve.reject(err);
                            }
                        });
                    
                    return wrappedPromise;
                }
            });

            window.confirmAction = (title, message, formIdOrCallback) => {
                let callback;
                if (typeof formIdOrCallback === 'string') {
                    callback = () => {
                        const form = document.getElementById(formIdOrCallback);
                        if (form) {
                            // Usar el loading store para enviar el formulario
                            Alpine.store('loading').submitForm(form);
                        }
                    };
                } else if (typeof formIdOrCallback === 'function') {
                    callback = formIdOrCallback;
                } else {
                    callback = () => {};
                }
                Alpine.store('confirm').open(message, callback);
            };

            @if(session('success'))
                Alpine.store('toast').add(@json(session('success')), 'success');
            @endif

            @if($errors->any())
                @foreach($errors->all() as $error)
                    Alpine.store('toast').add(@json($error), 'error');
                @endforeach
            @endif

            @if(session('info'))
                Alpine.store('toast').add(@json(session('info')), 'info');
            @endif
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @if($showSidebar ?? true)
    <div class="flex h-screen overflow-hidden w-full" x-data>
        
        @include('sidebar')

        <div class="flex flex-col flex-1 min-w-0 overflow-hidden relative">
            
            <header class="lg:hidden flex items-center justify-between p-4 bg-slate-900 text-white shadow-md z-30 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="bg-blue-600 p-2 rounded-xl shadow-lg shadow-blue-500/20 shrink-0">
                        <i class="fa-solid fa-hospital text-white"></i>
                    </div>
                    <span class="text-xl font-bold tracking-tight">HOSPITAL <span class="text-blue-500">TG</span></span>
                </div>
                <button @click="$store.sidebar.toggleMobile()" class="p-2 text-slate-400 hover:text-white rounded-lg focus:outline-none">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
            </header>

            <main class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-10 w-full relative">
                @yield('content')
            </main>
        </div>
    </div>
    @else
    <main class="min-h-screen">
        @yield('content')
    </main>
    @endif

    <x-confirm-modal />
    <x-loading-overlay />   {{-- ← NUEVO COMPONENTE DE CARGA --}}

    @stack('modals')
    @stack('scripts')
    <x-mensajes-notificaciones />
</body>

</html>