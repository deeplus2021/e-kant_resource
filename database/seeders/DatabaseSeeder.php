<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\Staff::factory(10)->create();
        $this->call([
            StaffStatusTableSeeder::class,
            StaffRolesTableSeeder::class,
            StaffTableSeeder::class,
            PageMenuTableSeeder::class,
            RolePermissionsTableSeeder::class,
            FieldTableSeeder::class,
            StaffFieldTableSeeder::class,
            StaffAddressTableSeeder::class,
        ]);
    }
}
