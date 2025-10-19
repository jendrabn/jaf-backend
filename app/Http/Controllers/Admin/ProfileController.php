<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class ProfileController extends Controller
{
    /**
     * Displays the user's profile page.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $roles = Role::pluck('name', 'id');

        return view('admin.profile', compact('user', 'roles'));
    }

    /**
     * Updates the user's profile information.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateProfile(ProfileRequest $request)
    {
        $request->user()->update($request->validated());

        if ($request->hasFile('avatar')) {
            $request->user()->clearMediaCollection(User::MEDIA_COLLECTION_NAME);
            $request->user()->addMediaFromRequest('avatar')
                ->toMediaCollection(User::MEDIA_COLLECTION_NAME);
        }

        toastr('Profile updated successfully.', 'success');

        return back();
    }

    /**
     * Update the user's password.
     *
     * @param  ProfileRequest  $request  -
     */
    public function updatePassword(ProfileRequest $request): RedirectResponse
    {
        $request->user()->update($request->only('password'));

        toastr('Password updated successfully.', 'success');

        return back();
    }
}
