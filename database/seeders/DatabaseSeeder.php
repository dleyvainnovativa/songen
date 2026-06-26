<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Orquestador de seeders.
 *
 * Orden importa: primero catálogos (especialidades, etc.), luego el admin
 * (que se liga al establecimiento y puede referenciar catálogos).
 *
 * Si ya tenías un DatabaseSeeder propio, fusiona: basta con agregar las dos
 * llamadas $this->call(...) de abajo a tu método run().
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CatalogosSeeder::class,
            AdminSeeder::class,
        ]);
    }
}
