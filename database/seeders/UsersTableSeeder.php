<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name' => 'Maftuna',
                'email' => 'maftuna@gmail.com',
                'role' => 'seller',
                'password' => Hash::make('maftunaseller123123'),
            ],
            [
                'name' => 'Sevinch',
                'email' => 'sevinch@gmail.com',
                'role' => 'seller',
                'password' => Hash::make('sevinch123123'),
            ],
            [
                'name' => 'Shakhzoda',
                'email' => 'shakhzoda@gmail.com',
                'role' => 'seller',
                'password' => Hash::make('shakhzoda1122'),
            ],
        ]);
    }
}
