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
    // reģistrē jaunu lietotāju un izveido piekļuves tokenu
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

        // tiek izveidots lietotāja ieraksts ar droši saglabātu paroli
        $user = User::create([
            'name' => $request->name,
            'surname' => $request->surname,
            'birth_date' => $request->birth_date,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
        ]);

        // pēc reģistrācijas uzreiz izveido autentifikācijas tokenu
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'message' => 'Registration successful'
        ], 201);
    }

    // pārbauda ievadītos datus un pieslēdz lietotāju
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // auth::attempt pārbauda, vai e-pasts un parole sakrīt ar datubāzi
        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // pēc veiksmīgas autorizācijas tiek nolasīts lietotājs un izveidots tokenis
        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'message' => 'Login successful'
        ]);
    }

    // dzēš aktīvo tokenu un beidz sesiju
    public function logout(Request $request)
    {
        // tiek dzēsts tikai pašreizējais autentifikācijas tokens
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    // atgriež pašlaik autorizētā lietotāja datus
    public function getUser(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
            'message' => 'user data retrieved successfully'
        ]);
    }

    // atjauno lietotāja profila pamatdatus
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

        // saglabā tikai tos laukus, kas redzami profilā
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

    // nosūta paroles atjaunošanas saiti uz lietotāja e-pastu
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found.'
            ], 404);
        }

        // izveido pagaidu atiestatīšanas tokenu
        $token = \Str::random(60);

        // saglabā tokenu datubāzē, lai to varētu pārbaudīt vēlāk
        \DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($token),
                'created_at' => now()
            ]
        );

        // izveido saiti uz frontend paroles maiņas lapu
        $resetUrl = "http://localhost:3000/reset-password?token={$token}&email=" . urlencode($request->email);

        // nosūta e-pastu ar paroles atiestatīšanas saiti
        try {
            Mail::send('emails.password-reset', [
                'resetUrl' => $resetUrl,
                'email' => $request->email
            ], function ($message) use ($request) {
                $message->to($request->email);
                $message->subject('Reset Your Password - Motion Dance Company');
            });

            return response()->json([
                'message' => 'Password reset link sent to your email.'
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Failed to send password reset email: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to send email. Please try again later.'
            ], 500);
        }
    }

    // pārbauda tokenu un nomaina lietotāja paroli
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/|confirmed',
        ]);

        // laravel pats pārbauda tokena derīgumu un veic paroles nomaiņu
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

        // ja tokens vai dati nav derīgi, atgriež kļūdu
        return response()->json([
            'message' => 'Failed to reset password. Please check your token and try again.'
        ], 400);
    }
}