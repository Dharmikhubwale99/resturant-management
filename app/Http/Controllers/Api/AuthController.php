<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                Log::warning('Login failed: Invalid credentials', [
                    'email' => $request->email,
                ]);

                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            $token = $user->createToken('api-token')->plainTextToken;

            $response = [
                'message' => 'Login successful',
                'user' => $user,
                'token' => $token,
            ];

            Log::info('User logged in successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return response()->json($response);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed during login', [
                'errors' => $e->errors(),
                'input' => $request->only(['email']),
            ]);

            throw $e; // rethrow so Laravel handles the error response
        } catch (\Throwable $e) {
            Log::critical('Unexpected login error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['message' => 'Something went wrong.'], 500);
        }
    }

    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'mobile' => 'required|digits:10|unique:users',
                'password' => 'required|string|min:6|confirmed',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'password' => bcrypt($request->password),
            ]);

            $user->assignRole('admin');

            $token = $user->createToken('api-token')->plainTextToken;

            Log::info('User registered successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return response()->json([
                'message' => 'Registration successful',
                'user' => $user,
                'token' => $token,
            ]);
        } catch (ValidationException $e) {
            Log::error('Validation failed during registration', [
                'errors' => $e->errors(),
                'input' => $request->only(['name', 'email', 'mobile']),
            ]);
            throw $e;
        } catch (\Throwable $e) {
            Log::critical('Unexpected registration error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['message' => 'Something went wrong.'], 500);
        }
    }
}
