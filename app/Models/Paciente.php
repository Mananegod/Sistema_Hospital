<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    use HasFactory;

    protected $table = 'pacientes';

    // Se agregaron los campos específicos del F15 y Hoja de Servicio
    protected $fillable = [
        'cedula', 
        'nombres', 
        'apellidos', 
        'edad', 
        'servicio', 
        'servicio_cod', // Nuevo: Código 53/54
        'sala_cama',    // Nuevo: Ubicación física
        'n_comprobante', // Nuevo: Control de almacén
        'fecha_ingreso', 
        'diagnostico', 
        'tratamiento', 
        'de_alta'
    ];

    protected $casts = [
        'de_alta' => 'boolean',
        'fecha_ingreso' => 'date',
    ];
}