<?php

use App\Http\Controllers\AlmacenController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MedicamentoController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\PersonalController;
use App\Http\Controllers\EstadisticaController;
use App\Http\Controllers\AlertasController;
use App\Http\Controllers\EpidemiologiaController;
use Illuminate\Support\Facades\Route;

// Autenticación
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Dashboard Principal
Route::get('/home', function () {
    return view('home');
})->name('home');

// Módulo de Inventario (Medicamentos)
Route::get('/inventario', [MedicamentoController::class, 'index'])->name('medicamentos.index');
Route::post('/inventario', [MedicamentoController::class, 'store'])->name('medicamentos.store');
Route::put('/inventario/{id}', [MedicamentoController::class, 'update'])->name('medicamentos.update');
Route::delete('/inventario/{id}', [MedicamentoController::class, 'destroy'])->name('medicamentos.destroy');

// Módulo de Personal
Route::get('/personal', [PersonalController::class, 'index'])->name('personal.index');
Route::post('/personal', [PersonalController::class, 'store'])->name('personal.store');
Route::put('/personal/{id}', [PersonalController::class, 'update'])->name('personal.update');
Route::patch('/personal/{id}/status', [PersonalController::class, 'toggleStatus'])->name('personal.status');
Route::delete('/personal/{id}', [PersonalController::class, 'destroy'])->name('personal.destroy');

// Bitácora de Auditoría
Route::get('/bitacora', [PersonalController::class, 'bitacora'])->name('personal.bitacora');

// Módulo de Almacen
Route::get('/almacen', [AlmacenController::class, 'index'])->name('almacen.index');
Route::post('/almacen/movimiento', [AlmacenController::class, 'registrarMovimiento'])->name('almacen.movimiento');
Route::post('/almacen/medicamento', [AlmacenController::class, 'storeMedicamento'])->name('almacen.store');
Route::post('/inventario/importar', [AlmacenController::class, 'importarExcel'])->name('inventario.import');
Route::post('/almacen/entrada-rapida', [AlmacenController::class, 'entradaRapida'])->name('stock.entrada');
Route::get('/api/medicamentos/buscar', [AlmacenController::class, 'buscarMedicamentos'])->name('medicamentos.buscar');
// Módulo de Retiros
Route::get('/retiros', [AlmacenController::class, 'indexRetiros'])->name('retiros.index');
Route::post('/retiros/procesar', [AlmacenController::class, 'procesarRetiro'])->name('retiros.procesar');
Route::post('/almacen/movimiento', [AlmacenController::class, 'registrarMovimiento'])->name('almacen.movimiento');

// modulo de pacientes xd
Route::get('/pacientes', [PacienteController::class, 'index'])->name('pacientes.index');
Route::post('/pacientes', [PacienteController::class, 'store'])->name('pacientes.store');
Route::put('/pacientes/{id}', [PacienteController::class, 'update'])->name('pacientes.update');

// notificaciones
Route::get('/notificaciones', function () {
    return view('notificaciones');
})->name('notificaciones.index');

Route::get('/almacen/retiros', [AlmacenController::class, 'indexRetiros'])->name('almacen.retiros');
Route::post('/almacen/retiros', [AlmacenController::class, 'guardarRetiro'])->name('almacen.retiros.store');

Route::get('/pacientes', [PacienteController::class, 'index'])->name('pacientes.index');
Route::post('/pacientes', [PacienteController::class, 'store'])->name('pacientes.store');
Route::get('/pacientes/{id}/pdf', [PacienteController::class, 'generarPdf'])->name('pacientes.pdf');
Route::post('/pacientes/{id}/update', [PacienteController::class, 'update'])->name('pacientes.update');
Route::post('/pacientes/{id}/delete', [PacienteController::class, 'delete'])->name('pacientes.delete');
Route::get('/pacientes/{id}/pdf', [PacienteController::class, 'imprimirPdf'])->name('pacientes.pdf');



Route::get('/estadisticas', [EstadisticaController::class, 'index'])->name('estadisticas.index');

Route::get('/alertas', [AlertasController::class, 'index'])->name('alertas.index');


Route::post('/almacen/vencimiento-masivo', [AlmacenController::class, 'actualizarVencimientoMasivo'])->name('almacen.vencimientoMasivo');

Route::get('/epidemiologia', [EpidemiologiaController::class, 'index'])->name('epidemiologia.index');
Route::post('/epidemiologia', [EpidemiologiaController::class, 'store'])->name('epidemiologia.store');
Route::delete('/epidemiologia/{id}', [EpidemiologiaController::class, 'destroy'])->name('epidemiologia.destroy');