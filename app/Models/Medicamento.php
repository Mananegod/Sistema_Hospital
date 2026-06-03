<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medicamento extends Model
{
    protected $table = 'medicamentos'; 

    protected $fillable = [
    'nombre', 
    'nombre_medicamento', 
    'cantidad_stock', 
    'area_destino', 
    'fecha_vencimiento', 
    'codigo_lote',
    'status_disponibilidad'
    ];
}