<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Personal; // Importación explícita recomendada

class User extends Authenticatable
{
    protected $table = 'usuarios';

    protected $fillable = [
        'nombre',
        'password',
        'personal_id',
    ];

    protected $hidden = [
        'password',
        'remember_token', // Es recomendable incluir remember_token por seguridad en autenticación
    ];

    /**
     * Relación con el registro de personal.
     */
    public function personal()
    {
        return $this->belongsTo(Personal::class, 'personal_id');
    }

    /**
     * Valida si el usuario es Administrador.
     */
    public function isAdmin(): bool
    {
        // Si no tiene registro en personal pero es el admin principal (ej. el creado por seeder/migración sin personal_id)
        if (!$this->personal_id) {
            return $this->nombre === 'Admin'; // O ajusta según tu lógica para el admin maestro
        }

        return $this->personal && $this->personal->tipo_usuario === 'Admin';
    }

    /**
     * Valida si el usuario es un Usuario estándar.
     */
    public function isUsuario(): bool
    {
        return $this->personal && $this->personal->tipo_usuario === 'Usuario';
    }
}