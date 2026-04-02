<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\Menu;

class AdminFeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Barcode Permissions
        $barcodePermissions = [
            'barcodes',
            'barcode_create',
            'barcode_edit',
            'barcode_delete',
            'barcode_show'
        ];

        foreach ($barcodePermissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'sanctum']);
        }

        // 2. Assign to Admin Role
        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole) {
            // Assign Barcode Permissions
            $adminRole->givePermissionTo($barcodePermissions);

            // Ensure Damage Permissions are assigned
            $damagePermissions = [
                'damages',
                'damage_create',
                'damage_edit',
                'damage_delete',
                'damage_show'
            ];
            $adminRole->givePermissionTo($damagePermissions);
        }

        // 3. Add Barcodes Menu Entry
        // Product & Stock parent is ID: 2
        Menu::firstOrCreate(
            ['url' => 'barcodes'],
            [
                'name'      => 'Barcodes',
                'language'  => 'barcodes',
                'icon'      => 'lab lab-barcode', // A barcode icon
                'status'    => 1,
                'parent'    => 2,
                'type'      => 1,
                'priority'  => 110, // A priority after damages (100)
            ]
        );

        echo "Admin features (Barcodes & Damages) activated successfully!\n";
    }
}
