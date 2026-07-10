<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioAdminSeeder extends Seeder
{
    public function run(): void
    {
        
        $user = DB::table('usuarios')
            ->whereRaw('LOWER(nombre) = ?', [strtolower('Admin')])
            ->first();

        if ($user) {
          
            DB::table('usuarios')
                ->where('id', $user->id)
                ->update([
                    'password' => Hash::make('1234'),
                    'updated_at' => now(),
                ]);
        } else {
           
            DB::table('usuarios')->insert([
                'nombre' => 'Admin',
                'password' => Hash::make('1234'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}