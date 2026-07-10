<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'staff', 'label' => 'Staff'],
            ['name' => 'spv', 'label' => 'Supervisor'],
            ['name' => 'manager', 'label' => 'Manager'],
            ['name' => 'direktur', 'label' => 'Direktur'],
            ['name' => 'finance', 'label' => 'Finance'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['name' => $role['name']], $role);
        }
    }
}
