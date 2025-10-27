<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\RoleDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoleRequest;
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

    public function store(RoleRequest $request)
    {
        $data = $request->validated();

        $role = Role::create([
            'name' => $data['name'],
        ]);

        $role->syncPermissions($data['permissions']);

        toastr('Role created successfully.', 'success');

        return redirect()->route('admin.roles.index');
    }

    public function edit(Role $role)
    {
        $role->load('permissions');

        $permissions = config('permission.permissions');

        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    public function update(RoleRequest $request, Role $role)
    {
        $data = $request->validated();

        $role->update([
            'name' => $data['name'],
        ]);

        $role->syncPermissions($data['permissions']);

        toastr('Role updated successfully.', 'success');

        return redirect()->back();
    }

    public function destroy(Role $role)
    {
        $role->delete();

        toastr('Role deleted successfully.', 'success');

        return redirect()->route('admin.roles.index');
    }
}
