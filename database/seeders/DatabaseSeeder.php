<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            CategoryElementSeeder::class,
            RolesAndPermissionsSeeder::class,
            AdminUserSeeder::class,
            ProjectSeeder::class,
            ProjectModuleSeeder::class,
            IsoTaskSeeder::class,
            TaskSeeder::class,
        ]);
    }
}
