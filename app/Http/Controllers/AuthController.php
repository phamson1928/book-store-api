<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class AuthController extends Controller
{
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
        $user->update([
            'last_login_at' => now()
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'status' => 'success',
            'message' => 'Đăng nhập thành công',
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Đăng xuất thành công'
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'user' => $request->user()
        ]);
    }

    public function tokens(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'tokens' => $request->user()->tokens
        ]);
    }

    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Đã xóa tất cả tokens'
        ]);
    }
    // Gửi email reset mật khẩu
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        // Tạo token
        $token = Str::random(64);

        // Lưu vào DB
        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($token),
                'created_at' => Carbon::now()
            ]
        );

        // Gửi email (ở đây demo gửi token luôn, thực tế gửi link)
        Mail::raw("Mã reset password của bạn: {$token}", function ($message) use ($request) {
            $message->to($request->email)
                    ->subject('Đặt lại mật khẩu');
        });

        return response()->json(['message' => 'Đã gửi email reset mật khẩu']);
    }

    // Đặt lại mật khẩu mới
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required',
            'password' => 'required|min:6|confirmed'
        ]);

        $record = DB::table('password_resets')->where('email', $request->email)->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return response()->json(['error' => 'Token không hợp lệ'], 400);
        }

        // Cập nhật mật khẩu user
        User::where('email', $request->email)->update([
            'password' => Hash::make($request->password)
        ]);

        // Xóa token reset
        DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Đặt lại mật khẩu thành công']);
    }
} 