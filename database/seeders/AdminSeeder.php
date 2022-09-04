<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('users')->insert([
            'first_name'=>'superadmin',
            'email' => 'superadmin@gmail.com',
            'password' => bcrypt('Admin@123'),
            'role_id' => 1,
            'mobile_no' => 1234567890,
            'status' => 'active'
        ]);
    }
}
