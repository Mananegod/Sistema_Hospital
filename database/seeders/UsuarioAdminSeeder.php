<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsuarioAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Insertamos usando estrictamente los campos de tu migración
        User::firstOrCreate([
            'nombre'   => 'Admin',
            'password' => Hash::make('1234')
        ]);
    }
}