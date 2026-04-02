<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleTableSeeder extends Seeder
{
    public function run()
    {
        $roles = ['Admin', 'Customer', 'Manager', 'POS Operator', 'Stuff'];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role, 'guard_name' => 'sanctum']
            );
        }
    }
}
