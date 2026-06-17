<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(SectorSeeder::class);
        $this->call(AreaSeeder::class);
        $this->call(UsuarioAdminSeeder::class);
    }
}