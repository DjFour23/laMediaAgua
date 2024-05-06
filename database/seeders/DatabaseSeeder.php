<?php

use Database\Seeders\CouponSeeder;
use Database\Seeders\SettingTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        DB::beginTransaction();

        try {
            // Lógica para ejecutar los seeders
            DB::commit();
            $this->call(UsersTableSeeder::class);
            $this->call(SettingTableSeeder::class);
            $this->call(CouponSeeder::class);

        } catch (\Exception $e) {
            DB::rollBack();
            // Manejo de errores
        }


    }
}
