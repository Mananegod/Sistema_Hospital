<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicamento extends Model
{
    use HasFactory;

    protected $table = 'medicamentos';

    protected $fillable = [
        'nombre',
        'nombre_medicamento',
        'cantidad_stock',
        'stock_minimo',
        'tipo_insumo',
        'codigo_lote',
        'fecha_vencimiento',
        'status_disponibilidad',
    ];
}