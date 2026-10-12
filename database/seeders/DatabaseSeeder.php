<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with Northwind Ceramics demo state.
     */
    public function run(): void
    {
        $this->call([
            Sprint3DemoSeeder::class,
        ]);
    }
}
