<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $rawPassword = (string) $request->input('password');
        $rawPasswordConfirmation = (string) $request->input('password_confirmation');

        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
            'name' => trim((string) $request->input('name')),
            'password' => trim($rawPassword),
            'password_confirmation' => trim($rawPasswordConfirmation),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        $token = $user->createToken('spa')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $rawPassword = (string) $request->input('password');

        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();
        $password = $validated['password'];
        $trimmedPassword = trim($rawPassword);
        $passwordMatches = $user
            && (Hash::check($password, $user->password)
                || ($trimmedPassword !== $password && Hash::check($trimmedPassword, $user->password)));

        if (! $passwordMatches) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        $token = $user->createToken('spa')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        if ($validated === []) {
            return response()->json([
                'message' => 'Ühtegi välja ei saadetud.',
            ], 422);
        }

        $user->fill($validated);
        $user->save();

        return response()->json([
            'message' => 'Profiil uuendatud.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $rawCurrentPassword = (string) $request->input('current_password');
        $rawPassword = (string) $request->input('password');
        $rawPasswordConfirmation = (string) $request->input('password_confirmation');

        $request->merge([
            'current_password' => trim($rawCurrentPassword),
            'password' => trim($rawPassword),
            'password_confirmation' => trim($rawPasswordConfirmation),
        ]);

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();

        if (! Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'Praegune salasõna on vale.',
            ], 422);
        }

        $user->password = $validated['password'];
        $user->save();

        return response()->json([
            'message' => 'Salasõna uuendatud.',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $rawCurrentPassword = (string) $request->input('current_password');
        $request->merge([
            'current_password' => trim($rawCurrentPassword),
        ]);

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
        ]);

        $user = $request->user();
        if (! Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'Praegune salasõna on vale.',
            ], 422);
        }

        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'message' => 'Konto kustutatud.',
        ]);
    }
}
