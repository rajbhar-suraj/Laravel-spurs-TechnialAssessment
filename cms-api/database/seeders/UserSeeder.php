<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('admin123'),
            'role' => 'admin', 
        ]);

        // Author user
        User::create([
            'name' => 'Author User',
            'email' => 'author@example.com',
            'password' => bcrypt('author123'),
            'role' => 'author', 
        ]);
    }
}
