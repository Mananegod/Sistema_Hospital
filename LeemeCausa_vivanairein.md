# Explicación de como usar el componente modal...

# 1. La plantilla de app (donde guardamos el head de la pagina)

causa, mira, ya tenemos el layouts/plantilla del cuerpo de la pagina, la plantilla se llama (app.blade.php) y su ubicacón esta en: resources\views\layouts\app.blade.php
dicho archivo guarda en su configuración de alpine.js su estado en store, el estado de los modales para abrirlos y cerralos con su información dentro, esto no hay necesidad de tocarlo,
esta plantilla tambien tiene toda la información de tu <head> y <body> y <styles>, por lo que, funciona como si fuera un componente para las nuevas vistas que vayas a crear, dicha plantilla tambien guarda el sidebar, por lo que, donde sea que llames a esta plantilla, el sidebar ya estara disponible.

para usar dicha plantilla en una nueva vista, hacemos lo siguiente:

ejemplo: Digamos que estamos creando la vista de perfil. Nosotros, para que se traiga la informacion de la plantilla debemos colocar la regla @extends.

@extends ('layouts.app') = esta regla la colocamos al principio de nuestra view, @eextends llama al componente o plantilla que creamos y queramos usar, el layouts.app es como decir, busca en la carpeta layouts el archivo llamado app.blade.php

@section('title', 'Mi Perfil') = esta regla se pone despues de extends, para decirle a la pagina que, cuando el usuario ingrese a dicha view, el titulo de la pagina cambie a Mi Perfil.

@section('content') = el @section('content'), le estamos diciendo a la app, que dentro de la section content, ira el contenido que se mostrara a la derecha de nuestro sidebar, es decir el contenido que mostrara el sidebar al clickearlo.

@endsection = el endsection cierra las sections abiertas de nuestra view.

# 2. USAR EL COMPONENTE DE MODAL PARA VER Y EDITAR Y EL COMPONENTE DE MODAL DE CONFIRMACIÓN.

Paso 1: En tu vista, incluye el modal con el contenido que quieras:
Usa el componente <x-modal> con un id único y dentro pon lo que se vera, en resumen, se vera algo como esto:

<x-modal id="tu-id" title="el titulo que mostrara el modal">

aqui dentro del componente ya colocas el html que quieras

<button @click="$store.modal.close()">Cerrar</button> = esto es importante, ya que este boton cierra el modal
(el boton se cierra con el atributo click de alpine.js, usando $store.modal.close() esta funcion en un boton es para clickear y cerrar el modal.)
</x-modal>

{{-- EJEMPLO: modal para ver detalles de un libro --}}

<x-modal id="verLibro" title="Detalles del Libro" maxWidth="max-w-md"> 
    <p><strong>Título:</strong> <span x-text="$store.modal.data.titulo"></span></p>
    <p><strong>Autor:</strong> <span x-text="$store.modal.data.autor"></span></p>
    <div class="mt-4">
        <button @click="$store.modal.close()" class="bg-blue-600 text-white px-4 py-2 rounded-xl">Cerrar</button>
    </div>
</x-modal>

# 3. MODAL DE CONFIRMACIÓN (para acciones peligrosas: eliminar, desactivar, etc.):

Paso 1: Crea un formulario oculto con un id único

<form id="eliminar-libro-{{ $libro->id }}" action="{{ route('libros.destroy', $libro->id) }}" method="POST" style="display: none;">
    @csrf @method('DELETE')
</form>

Paso 2: En el botón, usa la función global: confirmAction(titulo, mensaje, idDelFormulario)

<button type="button"
@click="confirmAction('Eliminar libro', '¿Seguro que quieres eliminar {{ $libro->titulo }}?', 'eliminar-libro-{{ $libro->id }}')">
Eliminar
</button>


ahora, el modal de confirmación aparecerá con tu mensaje. Si el usuario confirma, se enviará el formulario.

