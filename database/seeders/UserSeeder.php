<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ✅ Admin
        User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => Hash::make('admin1234'),
            'role' => 'admin',
        ]);

        // ✅ Client
        User::create([
            'name' => 'Client Test',
            'email' => 'client@test.com',
            'password' => Hash::make('client1234'),
            'role' => 'client',
        ]);
    }
}
