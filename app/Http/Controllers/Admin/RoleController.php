<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\RoleDataTable;
use App\Enums\Role as EnumsRole;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(RoleDataTable $dataTable)
    {
        return $dataTable->render('admin.roles.index');
    }

    public function create()
    {
        $permissions = config('permission.permissions');

        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles'],
            'permissions' => ['required', 'array'],
        ]);

        $role = Role::create([
            'name' => str()->snake($request->name, '_'),
        ]);

        $role->syncPermissions($request->permissions);

        toastr('Role created successfully.', 'success');

        return redirect()->route('admin.roles.index');
    }

    public function edit(Role $role)
    {
        $role->load('permissions');

        $permissions = config('permission.permissions');

        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,' . $role->id],
            'permissions' => ['required', 'array'],
        ]);

        $role->update([
            'name' => str()->snake($request->name, '_'),
        ]);

        $role->syncPermissions($request->permissions);

        toastr('Role updated successfully.', 'success');
    }

    public function destroy(Role $role)
    {
        $role->delete();

        toastr('Role deleted successfully.', 'success');

        return redirect()->route('admin.roles.index');
    }
}
