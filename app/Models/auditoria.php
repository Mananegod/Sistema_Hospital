<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    use HasFactory;

    // Indicamos el nombre de la tabla (opcional si sigue el plural, pero mejor asegurar)
    protected $table = 'auditorias';

    // Definimos los campos que se pueden llenar masivamente
    protected $fillable = [
        'modulo',
        'accion',
        'descripcion',
        'usuario'
    ];

    // Esto asegura que las fechas se traten como objetos Carbon
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}