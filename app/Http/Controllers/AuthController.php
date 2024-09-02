<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        // Set role based on email
        $role = $validated['email'] === 'admin@admin.com' ? 'admin' : 'user';

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $role,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return (new UserResource($user))->additional([
            'token' => $token,
        ]);
    }

    /**
     * Login an existing user.
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = Auth::user();

        // Pastikan user adalah instance dari User model
        if (!($user instanceof User)) {
            throw new \Exception('User instance is not of correct type.');
        }

        // Pastikan metode createToken ada
        if (!method_exists($user, 'createToken')) {
            throw new \Exception('createToken method does not exist on User model.');
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return (new UserResource($user))->additional([
            'token' => $token,
        ]);
    }

    /**
     * Handle a forgot password request.
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return response()->json(['status' => __($status)]);
    }

    /**
     * Handle a password reset request.
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        $status = Password::reset(
            $request->validated(),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();

                $user->tokens()->delete();
            }
        );

        return response()->json(['status' => __($status)]);
    }

    /**
     * Get the authenticated user.
     */
    public function me(Request $request)
    {
        return new UserResource($request->user());
    }
}
