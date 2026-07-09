<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioAdminSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('usuarios')->updateOrInsert(
            ['nombre' => 'ADMIN'],
            [
                'password' => Hash::make('1234'),
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
    }
}