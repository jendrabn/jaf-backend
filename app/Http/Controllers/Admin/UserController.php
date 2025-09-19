<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\UsersDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param UsersDataTable $dataTable
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(UsersDataTable $dataTable)
    {
        return $dataTable->render('admin.users.index');
    }

    /**
     * Create a new user.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        $roles = Role::pluck('name', 'id');

        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user in the database.
     *
     * @param UserRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(UserRequest $request)
    {
        $user = User::create($request->validated());
        $user->assignRole($request->validated('roles'));

        toastr('User created successfully.', 'success');

        return redirect()->route('admin.users.index');
    }


    /**
     * Display the specified user.
     *
     * @param User $user
     * @return \Illuminate\Contracts\View\View
     */
    public function show(User $user)
    {
        $user->loadCount('orders');

        return view('admin.users.show', compact('user'));
    }

    /**
     * Edit a user.
     *
     * @param User $user
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(User $user)
    {
        $roles = Role::pluck('name', 'id');

        $user->load('roles');

        return view('admin.users.edit', compact('roles', 'user'));
    }

    /**
     * Updates a user with the provided data.
     *
     * @param UserRequest $request
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $user->update($request->validated());
        $user->syncRoles($request->validated('roles'));

        toastr('User updated successfully.', 'success');

        return back();
    }

    /**
     * Deletes a user by ID.
     *
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return response()->json(['message' => 'User deleted successfully.']);
    }

    /**
     * Deletes multiple users by IDs.
     *
     * @param UserRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function massDestroy(UserRequest $request): JsonResponse
    {
        $ids = $request->ids;
        $count = count($ids);

        User::whereIn('id', $ids)->delete();

        audit_log(
            event: 'bulk_deleted',
            description: 'admin:bulk_delete_audit_logs',
            before: null,
            after: null,
            extra: [
                'changed'    => ['ids' => $ids, 'count' => $count],
                'properties' => ['count' => $count],
                'meta' => ['note' => 'Bulk deleted ' . $count . ' users']
            ],
            subjectId: null,
            subjectType: \App\Models\User::class
        );

        return response()->json(['message' => 'Users deleted successfully.']);
    }
}
