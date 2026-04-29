<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MedicamentoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PersonalController;
use App\Http\Controllers\AlmacenController;

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

// Módulo de Retiros
Route::get('/retiros', [AlmacenController::class, 'indexRetiros'])->name('retiros.index');
Route::post('/retiros/procesar', [AlmacenController::class, 'procesarRetiro'])->name('retiros.procesar');
Route::post('/almacen/movimiento', [AlmacenController::class, 'registrarMovimiento'])->name('almacen.movimiento');