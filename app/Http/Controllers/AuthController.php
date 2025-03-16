<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:1',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'User  registered successfully'], 201);
    }

    public function login(Request $request)
    {
        // Validasi input
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
    
        $credentials = $request->only('email', 'password');
    
        // Log upaya login
        Log::info('Login attempt', ['email' => $request->email]);
    
        // Cek apakah pengguna ada di database
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            Log::warning('User  not found', ['email' => $request->email]);
            return response()->json(['error' => 'User  not found'], 404);
        }
    
        // Cek apakah password valid
        if (Hash::check($request->password, $user->password)) {
            // Jika password valid, buat token JWT
            try {
                $token = JWTAuth::fromUser ($user);
                Log::info('Login successful', ['email' => $request->email]);
                return response()->json(compact('token'));
            } catch (JWTException $e) {
                Log::error('Could not create token', ['error' => $e->getMessage()]);
                return response()->json(['error' => 'Could not create token'], 500);
            }
        } else {
            Log::warning('Invalid credentials', ['email' => $request->email]);
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
    }

    public function protectedRoute(Request $request){
        return response()->json(['message' => 'Rute yang dilindungi']);
    }
}