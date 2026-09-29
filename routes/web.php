<?php

use App\Http\Controllers\AlmacenController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MedicamentoController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\PersonalController;
use App\Http\Controllers\EstadisticaController;
use App\Http\Controllers\AlertasController;
use App\Http\Controllers\EpidemiologiaController;
use App\Http\Middleware\AuthHospital;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\TraficoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoteController;

// ==========================================
// RUTAS PÚBLICAS
// ==========================================
Route::get('/ping-sistema', [TraficoController::class, 'mantenerActivo']);

Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::get('/login', function() { return redirect('/'); }); 
Route::post('/login', [AuthController::class, 'login']);
Route::get('/almacen/lote/{codigo_lote?}', [AlmacenController::class, 'verPorLote'])->name('almacen.lote');

Route::get('/crear-admin-temporal', function() {
    try {
        $existe = User::where('nombre', 'Admin')->first();
        if ($existe) {
            return "El usuario Admin ya existe en la base de datos.";
        }
        User::create([
            'nombre'   => 'Admin',
            'password' => Hash::make('1234')
        ]);
        return "¡Usuario Admin creado con éxito desde el navegador!";
    } catch (\Exception $e) {
        return "Error al crear el usuario: " . $e->getMessage();
    }
});

// ==========================================
// RUTAS PROTEGIDAS (Bajo AuthHospital)
// ==========================================
Route::middleware([AuthHospital::class])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/home', function () {
        return view('home');
    })->name('home');

    // Inventario
    Route::get('/inventario', [MedicamentoController::class, 'index'])->name('medicamentos.index');
    Route::post('/inventario', [MedicamentoController::class, 'store'])->name('medicamentos.store');
    Route::put('/inventario/{id}', [MedicamentoController::class, 'update'])->name('medicamentos.update');
    Route::delete('/inventario/{id}', [MedicamentoController::class, 'destroy'])->name('medicamentos.destroy');

    // Personal
    Route::get('/personal', [PersonalController::class, 'index'])->name('personal.index');
    Route::post('/personal', [PersonalController::class, 'store'])->name('personal.store');
    Route::put('/personal/{id}', [PersonalController::class, 'update'])->name('personal.update');
    Route::patch('/personal/{id}/status', [PersonalController::class, 'toggleStatus'])->name('personal.status');
    Route::delete('/personal/{id}', [PersonalController::class, 'destroy'])->name('personal.destroy');
    Route::get('/bitacora', [PersonalController::class, 'bitacora'])->name('personal.bitacora');

    // Almacén y Movimientos
    Route::get('/almacen', [AlmacenController::class, 'index'])->name('almacen.index');
    Route::post('/almacen/movimiento', [AlmacenController::class, 'registrarMovimiento'])->name('almacen.movimiento');
    Route::post('/almacen/medicamento', [AlmacenController::class, 'storeMedicamento'])->name('almacen.store');
    Route::post('/inventario/importar', [AlmacenController::class, 'importarExcel'])->name('inventario.import');
    Route::post('/almacen/entrada-rapida', [AlmacenController::class, 'entradaRapida'])->name('stock.entrada');
    Route::get('/api/medicamentos/buscar', [AlmacenController::class, 'buscarMedicamentos'])->name('medicamentos.buscar');
    Route::post('/almacen/importar', [AlmacenController::class, 'importarExcel'])->name('inventario.import');
    Route::get('/almacen/pdf', [AlmacenController::class, 'exportarPdf'])->name('almacen.pdf');
    
    // Retiros
    Route::get('/retiros', [AlmacenController::class, 'indexRetiros'])->name('retiros.index');
    Route::post('/retiros/procesar', [AlmacenController::class, 'procesarRetiro'])->name('retiros.procesar');
    Route::get('/almacen/retiros', [AlmacenController::class, 'indexRetiros'])->name('almacen.retiros');
    Route::post('/almacen/retiros', [AlmacenController::class, 'guardarRetiro'])->name('almacen.retiros.store');
    Route::get('/almacen/retiros/pdf', [AlmacenController::class, 'pdf'])->name('almacen.retiros.pdf');

    // Pacientes
    Route::get('/pacientes', [PacienteController::class, 'index'])->name('pacientes.index');
    Route::post('/pacientes', [PacienteController::class, 'store'])->name('pacientes.store');
    Route::put('/pacientes/{id}', [PacienteController::class, 'update'])->name('pacientes.update');
    Route::post('/pacientes/{id}/update', [PacienteController::class, 'update'])->name('pacientes.update');
    Route::post('/pacientes/{id}/delete', [PacienteController::class, 'delete'])->name('pacientes.delete');
    Route::get('/pacientes/{id}/pdf', [PacienteController::class, 'imprimirPdf'])->name('pacientes.pdf');

    // Notificaciones y Alertas
    Route::get('/notificaciones', function () { return view('notificaciones'); })->name('notificaciones.index');
    Route::get('/alertas', [AlertasController::class, 'index'])->name('alertas.index');

    // Estadísticas
    Route::get('/estadisticas', [EstadisticaController::class, 'index'])->name('estadisticas.index');

    // Epidemiología
    Route::get('/epidemiologia', [EpidemiologiaController::class, 'index'])->name('epidemiologia.index');
    Route::post('/epidemiologia', [EpidemiologiaController::class, 'store'])->name('epidemiologia.store');
    Route::delete('/epidemiologia/{id}', [EpidemiologiaController::class, 'destroy'])->name('epidemiologia.destroy');
    Route::post('/epidemiologia/importar', [EpidemiologiaController::class, 'importar'])->name('epidemiologia.importar');
    Route::get('/epidemiologia/pdf', [EpidemiologiaController::class, 'pdf'])->name('epidemiologia.pdf');

    // Lotes
    Route::post('/almacen/vencimiento-masivo', [AlmacenController::class, 'actualizarVencimientoMasivo'])->name('almacen.vencimientoMasivo');
    Route::post('/almacen/editar-masivo', [AlmacenController::class, 'editarMasivo'])->name('almacen.editar-masivo');
    Route::post('/almacen/importar-insumos', [AlmacenController::class, 'importarInsumosExcel'])->name('insumos.import');
    Route::get('/almacen/buscar-insumos', [AlmacenController::class, 'buscarInsumos'])->name('insumos.buscar');
    Route::post('/almacen/importar-medicamentos', [AlmacenController::class, 'importarExcel'])->name('inventario.import');
    Route::get('/lotes', [LoteController::class, 'index'])->name('lotes.index');
    Route::get('/lotes/{id}', [LoteController::class, 'show'])->name('lotes.show');
    Route::put('/lotes/{id}/estado', [LoteController::class, 'updateEstado'])->name('lotes.update-estado');
    Route::get('/personal/pdf', [PersonalController::class, 'exportPdf'])->name('personal.pdf');
});