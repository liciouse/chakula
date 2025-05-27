<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Create Admin User
        DB::table('users')->insert([
            'name' => 'Admin User',
            'email' => 'admin@foodblog.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create Editor User
        DB::table('users')->insert([
            'name' => 'Editor User',
            'email' => 'editor@foodblog.com',
            'password' => Hash::make('password'),
            'role' => 'editor',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create Author User
        DB::table('users')->insert([
            'name' => 'Author User',
            'email' => 'author@foodblog.com',
            'password' => Hash::make('password'),
            'role' => 'author',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create Regular User
        DB::table('users')->insert([
            'name' => 'Regular User',
            'email' => 'user@foodblog.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}