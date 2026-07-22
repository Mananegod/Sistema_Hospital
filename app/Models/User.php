<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

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
    ];

    public function personal()
    {
        return $this->belongsTo(Personal::class, 'personal_id');
    }

    public function isAdmin(): bool
    {
        return $this->personal && $this->personal->tipo_usuario === 'Admin';
    }

    public function isUsuario(): bool
    {
        return $this->personal && $this->personal->tipo_usuario === 'Usuario';
    }
}