<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['name' => 'Staff Test', 'email' => 'staff@test.com', 'role' => 'staff'],
            ['name' => 'SPV Test', 'email' => 'spv@test.com', 'role' => 'spv'],
            ['name' => 'Manager Test', 'email' => 'manager@test.com', 'role' => 'manager'],
            ['name' => 'Direktur Test', 'email' => 'direktur@test.com', 'role' => 'direktur'],
            ['name' => 'Finance Test', 'email' => 'finance@test.com', 'role' => 'finance'],
        ];

        foreach ($accounts as $acc) {
            $role = Role::where('name', $acc['role'])->firstOrFail();

            User::updateOrCreate(
                ['email' => $acc['email']],
                [
                    'name' => $acc['name'],
                    'phone' => '081234567890',
                    'password' => Hash::make('password'),
                    'role_id' => $role->id,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
