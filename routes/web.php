<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MedicamentoController;
use App\Http\Controllers\AuthController;

Route::get('/', [AuthController::class, 'showLogin'])->name('login');

Route::get('/home', function () {
    return view('home');
})->name('home');

Route::get('/inventario', [MedicamentoController::class, 'index'])->name('medicamentos.index');

Route::post('/login', [AuthController::class, 'login']);
Route::post('/inventario', [MedicamentoController::class, 'store'])->name('medicamentos.store');
Route::put('/inventario/{id}', [MedicamentoController::class, 'update'])->name('medicamentos.update');
Route::delete('/inventario/{id}', [MedicamentoController::class, 'destroy'])->name('medicamentos.destroy');