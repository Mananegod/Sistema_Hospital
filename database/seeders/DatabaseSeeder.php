<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(SectorSeeder::class);
        $this->call(AreaSeeder::class);
       /*manaze agrega aqui los seeders que vayas creando para que siempre se lean en el host */
    }
}