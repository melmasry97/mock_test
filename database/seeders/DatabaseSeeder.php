<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            CategoryElementSeeder::class,
            ProjectSeeder::class,
            ProjectModuleSeeder::class,
            IsoTaskSeeder::class,
            TaskSeeder::class,
            AdminUserSeeder::class,
        ]);
    }
}
