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
        'tipo_usuario',
        'especialidad', 
        'turno', 
        'telefono',
        'activo'
    ];

    public function usuario()
{
    return $table->hasOne(User::class, 'personal_id');
}
    protected $casts = [
        'activo' => 'boolean',
    ];
}