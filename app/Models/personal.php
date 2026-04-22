<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Personal extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'personal';

    protected $fillable = [
        'cedula', 
        'nombres', 
        'apellidos', 
        'cargo', 
        'especialidad', 
        'turno', 
        'telefono',
        'especialidad',
        'activo'
    ];
}