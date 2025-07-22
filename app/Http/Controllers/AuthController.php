<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Đăng ký user mới
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Đăng ký thành công',
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ], 201);
    }

    /**
     * Đăng nhập user
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Thông tin đăng nhập không chính xác.'],
            ]);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Đăng nhập thành công',
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    /**
     * Đăng xuất user
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Đăng xuất thành công'
        ]);
    }

    /**
     * Lấy thông tin user hiện tại
     */
    public function me(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'user' => $request->user()
        ]);
    }

    /**
     * Lấy tất cả tokens của user
     */
    public function tokens(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'tokens' => $request->user()->tokens
        ]);
    }

    /**
     * Xóa tất cả tokens của user
     */
    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Đã xóa tất cả tokens'
        ]);
    }
} 