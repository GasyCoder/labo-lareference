<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UsersSeeder;
use Database\Seeders\PermissionSeeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            UsersSeeder::class,
            AnalyseTypeSeeder::class,
            ExamenSeeder::class,
            AnalyseCsvSeeder::class,
            // AnalyseElementSeeder::class,
            BacteryFamiliesSeeder::class,
        ]);
    }
}