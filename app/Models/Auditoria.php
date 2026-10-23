<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Auditoria extends Model
{
    protected $table = 'auditorias'; // O el nombre de tu tabla si es diferente

    protected $fillable = [
        'modulo',
        'accion',
        'descripcion',
        'usuario',
    ];

    protected static function booted()
    {
        static::creating(function ($auditoria) {
            // Si no se ha especificado un usuario manualmente, se asigna el actual
            if (empty($auditoria->usuario)) {
                $auditoria->usuario = Auth::user()->nombre ?? 'Sistema';
            }
        });
    }
}