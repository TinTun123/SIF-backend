<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //

    public function register(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users,name',
            'email' => 'required|string|email|max:255|unique:users,email',
            'level' => 'required|integer|min:0|max:3',
            'password' => 'required|string|min:8|confirmed', // 'confirmed' expects 'password_confirmation'
        ]);

        // Create user
        $user = User::create([
            'name' => $validated['username'],
            'email' => $validated['email'],
            'level' => $validated['level'],
            'password' => Hash::make($validated['password']),
        ]);

        // Option 1: Return success response (no token)
        return response()->json([
            'message' => 'Registration successful',
            'user' => $user,
        ], 200);

        // Option 2 (if token-based): create a token
        // $token = $user->createToken('auth_token')->plainTextToken;
        // return response()->json(['token' => $token, 'user' => $user], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid login credentials'], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users,name',
            'level' => 'required|integer|min:0|max:3',
            'password' => 'nullable|string|min:8|confirmed', // 'confirmed' expects 'password_confirmation'
        ]);

        if ($request->user()->level != 1) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user->update([
            'name' => $validated['username'],
            'level' => $validated['level'],
            'password' =>  isset($validated['password']) ? Hash::make($validated['password']) : $user->password,
        ]);

        return response()->json([
            'message' => 'User info updated',
            'user' => $user,
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out'], 200);
    }

    public function destroy(Request $request, User $user)
    {
        if ($request->user()->level != 1) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully'], 200);
    }
}
