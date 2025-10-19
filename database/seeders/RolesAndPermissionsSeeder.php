<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = config('permission.permissions');
        foreach ($permissions as $module => $permissions) {
            foreach ($permissions as $permission) {
                Permission::create(['name' => $module.'.'.$permission]);
            }
        }

        $roleUser = Role::create(['name' => 'user']);
        $roleAdmin = Role::create(['name' => 'admin']);

        $roleAdmin->syncPermissions(Permission::all());
    }
}
