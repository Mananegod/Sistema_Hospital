<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MedicamentoController;

Route::get('/', function () {
    return view('home');
});

Route::get('/inventario', [App\Http\Controllers\MedicamentoController::class, 'index'])->name('medicamentos.index');
Route::post('/inventario', [App\Http\Controllers\MedicamentoController::class, 'store'])->name('medicamentos.store');
Route::delete('/inventario/{id}', [MedicamentoController::class, 'destroy'])->name('medicamentos.destroy');
Route::put('/inventario/{id}', [MedicamentoController::class, 'update'])->name('medicamentos.update');