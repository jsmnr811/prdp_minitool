<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class FakerUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
public function run(): void
{
    // 1. Create Roles
    $superAdminRole = Role::firstOrCreate(['name' => 'superadministrator']);
    $adminRole = Role::firstOrCreate(['name' => 'administrator']);
    $bidderRole = Role::firstOrCreate(['name' => 'bidder']);

    // 2. Create Permissions
    $manageSystem = Permission::firstOrCreate(['name' => 'manage system']);
    $placeBids = Permission::firstOrCreate(['name' => 'place bids']);

    // 3. Assign Permissions to Roles
    $adminRole->givePermissionTo($manageSystem);
    $bidderRole->givePermissionTo($placeBids);

    // Give all permissions to superadministrator
    $superAdminRole->syncPermissions(Permission::all());

    // 4. Create Superadmin User
    $superAdminUser = User::factory()->create([
        'name' => 'Super Admin',
        'email' => 'superadmin@example.com',
        'contact_number' => '1234567890', // Add a contact number
        'status' => 1, // Set status to active
    ]);
    $superAdminUser->assignRole($superAdminRole);

    // 5. Create Administrator User
    $adminUser = User::factory()->create([
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'contact_number' => '0987654321', // Add a contact number
        'status' => 1, // Set status to active
    ]);
    $adminUser->assignRole($adminRole);

    // 6. Create Bidder User
    $bidderUser = User::factory()->create([
        'name' => 'Bidder User',
        'email' => 'bidder@example.com',
        'contact_number' => '1122334455', // Add a contact number
        'status' => 1, // Set status to active
    ]);
    $bidderUser->assignRole($bidderRole);
}

}
