<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medicamento extends Model
{
    protected $table = 'medicamento'; 

    protected $fillable = [
        'codigo_lote',
        'nombre_medicamento',
        'cantidad_stock',
        'area_destino',
        'fecha_vencimiento',
        'status_disponibilidad',
    ];
}