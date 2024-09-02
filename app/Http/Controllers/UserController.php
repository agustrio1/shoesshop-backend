<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Get all users.
     */
    public function index()
    {
        return UserResource::collection(User::all());
    }

    /**
     * Get a user by ID.
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        return new UserResource($user);
    }

    /**
     * Get a user by email.
     */
    public function getByEmail(Request $request)
    {
        $user = User::where('email', $request->email)->firstOrFail();
        return new UserResource($user);
    }

    /**
     * Update a user.
     */
    public function update(UserUpdateRequest $request, $id)
    {
        $user = User::findOrFail($id);

        if (Auth::user()->role !== 'admin' && $request->has('role')) {
            return response()->json(['message' => 'Unauthorized to update role'], 403);
        }

        $this->authorize('update', $user);

        $user->update($request->validated());

        return new UserResource($user);
    }


    /**
     * Delete a user.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}
