<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password as PasswordRule; 

class AuthController extends Controller
{

    public function register(Request $request)
    {
        $request->validate([
        'name' => 'required|string|max:30|regex:/^[\p{L}\s\-\']+$/u',
        'surname' => 'required|string|max:30|regex:/^[\p{L}\s\-\']+$/u',
        'birth_date' => 'required|date|before:today',
        'email' => 'required|string|max:100|unique:users|regex:/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
        'phone_number' => 'required|string|max:20', 
        'password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/|confirmed',
    ]);

        $user = User::create([
            'name' => $request->name,
            'surname' => $request->surname,
            'birth_date' => $request->birth_date,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'message' => 'Registration successful'
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
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'message' => 'Login successful'
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    public function getUser(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
            'message' => 'user data retrieved successfully'
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'name' => 'required|string|max:30|regex:/^[\p{L}\s\-\']+$/u',
            'surname' => 'required|string|max:30|regex:/^[\p{L}\s\-\']+$/u',
            'email' => 'required|string|max:100|unique:users,email,' . $user->user_id . ',user_id|regex:/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            'phone_number' => 'required|string|max:20',
            'birth_date' => 'required|date|before:today'
        ]);

        $user->update([
            'name' => $request->name,
            'surname' => $request->surname,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'birth_date' => $request->birth_date
        ]);

        return response()->json([
            'message' => 'profile updated successfully',
            'user' => $user
        ]);
    }
    
    public function forgotPassword(Request $request)
    {
        \Log::info('=== FORGOT PASSWORD REQUEST START ===');
        \Log::info('Email requested: ' . $request->email);
        
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);
        
        \Log::info('Validation passed for email: ' . $request->email);
        
        $status = Password::sendResetLink(
            $request->only('email')
        );
        
        \Log::info('Password reset status: ' . $status);
        \Log::info('=== FORGOT PASSWORD REQUEST END ===');

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Password reset link sent to your email.'
            ], 200);
        }

        return response()->json([
            'message' => 'Unable to send reset link. Please try again.'
        ], 400);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ]);
                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Password has been reset successfully.'
            ], 200);
        }

        return response()->json([
            'message' => 'Failed to reset password. Please check your token and try again.'
        ], 400);
    }
}
