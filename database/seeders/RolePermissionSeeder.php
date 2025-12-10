<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        $permissions = [
            'manage_users',
            'manage_templates',
            'approve_templates',
            'access_admin_panel',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create Roles and assign permissions
        $roleAdmin = Role::firstOrCreate(['name' => 'admin']);
        $roleAdmin->givePermissionTo(Permission::all());

        $roleDesigner = Role::firstOrCreate(['name' => 'designer']);
        $roleDesigner->givePermissionTo(['manage_templates']);

        $roleUser = Role::firstOrCreate(['name' => 'user']);

        // Create Default Users
        $admin = User::firstOrCreate(
            ['email' => 'admin@eprinton.com'],
            [
                'name' => 'Admin User',
                'username' => 'admin',
                'phone' => '1111111111',
                'password' => Hash::make('12345678'),
                'is_active' => true,
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
            ]
        );
        $admin->assignRole($roleAdmin);

        $designer = User::firstOrCreate(
            ['email' => 'designer@eprinton.com'],
            [
                'name' => 'Designer User',
                'username' => 'designer',
                'phone' => '2222222222',
                'password' => Hash::make('12345678'),
                'is_active' => true,
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
            ]
        );
        $designer->assignRole($roleDesigner);

        $user = User::firstOrCreate(
            ['email' => 'user@eprinton.com'],
            [
                'name' => 'Demo User',
                'username' => 'user',
                'phone' => '3333333333',
                'password' => Hash::make('12345678'),
                'is_active' => true,
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
            ]
        );
        $user->assignRole($roleUser);
    }
}
