<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

use App\Models\User;
use App\Mail\SendOtpMail;
use App\Mail\SendResetPasswordMail; 

class AuthController extends Controller
{
public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if ($user->email_verified_at === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Anda belum diverifikasi. Silakan masukkan kode OTP.',
                    'data' => [
                        'is_verified' => false,
                        'email' => $user->email
                    ]
                ], 403); 
            }
            
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login Berhasil!',
                'data' => [
                    'user' => $user,
                    'token' => $token
                ]
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Email atau Password salah',
            ], 401);
        }
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users', 
            'password' => 'required|string|min:6', 
        ]);

        $otpCode = rand(1000, 9999);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password), 
            'otp' => $otpCode, 
        ]);

        try {
            Mail::to($user->email)->send(new SendOtpMail($otpCode));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal kirim email: ' . $e->getMessage(),
            ], 500);
        }
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registrasi Berhasil! Silakan cek email untuk OTP.',
            'data' => [
                'user' => $user,
                'token' => $token,
            ]
        ], 201);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp'   => 'required|digits:4',
        ]);

        $user = User::where('email', $request->email)->first();

  if ($user->otp && $user->otp == $request->otp) {
            $user->otp = null;
            $user->email_verified_at = now();
            $user->save();

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Verifikasi OTP berhasil!',
                'data'    => [
                    'user' => $user,
                    'token' => $token
                ]
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Kode OTP salah atau sudah tidak berlaku.',
        ], 400);
    }

    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Akun ini sudah terverifikasi. Silakan login.',
            ], 400);
        }

        $newOtp = rand(1000, 9999);
        $user->otp = $newOtp;
        $user->save();

        try {
            Mail::to($user->email)->send(new SendOtpMail($newOtp));
        } catch (\Exception $e) {
             return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim email: ' . $e->getMessage(),
            ], 500);
        }
        return response()->json([
            'success' => true,
            'message' => 'Kode OTP baru berhasil dikirim!',
        ], 200);
    }

    public function profile(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'message' => 'Data profile berhasil diambil',
            'data' => $user
        ], 200);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
        ]);

        if ($request->filled('new_password')) {
            $request->validate([
                'old_password' => 'required',
                'new_password' => 'required|min:6',
            ]);

            if (!Hash::check($request->old_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password lama yang Anda masukkan salah.',
                ], 422); 
            }

            $user->password = Hash::make($request->new_password);
        }
        if ($request->hasFile('image')) {
            if ($user->image && Storage::disk('public')->exists($user->image)) {
                Storage::disk('public')->delete($user->image);
            }
            $path = $request->file('image')->store('profile', 'public');
            
            $user->image = $path;
        }
        $user->name = $request->name;
        $user->email = $request->email;

        $user->save(); 

        return response()->json([
            'success' => true,
            'message' => 'Profile berhasil diperbarui!',
            'data' => $user
        ]);
    }
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout Berhasil'
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        $otpCode = rand(1000, 9999);
        $user->otp = $otpCode;
        $user->save();

        try {
            Mail::to($user->email)->send(new SendResetPasswordMail($otpCode));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim email: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Kode OTP untuk reset password telah dikirim ke email Anda!',
            'data' => [
                'email' => $user->email
            ]
        ], 200);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|digits:4',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->otp && $user->otp == $request->otp) {
            $user->password = Hash::make($request->password);
            $user->otp = null;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Password berhasil direset! Silakan login dengan password baru.',
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Kode OTP salah atau sudah tidak berlaku.',
        ], 400);
    }
}