<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Constants\ResponseCode;
use Exception;

class AuthController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string',
                'username' => 'required|string|unique:users',
                'email' => 'required|email|unique:users',
                'phone' => 'required|string|unique:users',
                'password' => 'required|string|min:6',
            ]);

            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'is_active' => false,
            ]);

            $user->assignRole('user');

            // Generate and Send OTP
            $otp = rand(100000, 999999);
            Cache::put('otp_' . $user->phone, $otp, 600); 

            return response()->json([
                'status' => ResponseCode::CREATED,
                'message' => 'User registered successfully. Please verify OTP.',
                'phone' => $user->phone,
                'dev_otp' => $otp 
            ], ResponseCode::CREATED);

        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function verifyOtp(Request $request)
    {
        try {
            $request->validate([
                'phone' => 'required|string',
                'otp' => 'required|integer',
            ]);

            $cachedOtp = Cache::get('otp_' . $request->phone);

            if (!$cachedOtp || $cachedOtp != $request->otp) {
                throw ValidationException::withMessages(['otp' => 'Invalid or expired OTP.']);
            }

            $user = User::where('phone', $request->phone)->firstOrFail();
            $user->is_active = true;
            $user->phone_verified_at = now();
            $user->save();

            Cache::forget('otp_' . $request->phone);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => ResponseCode::SUCCESS,
                'message' => 'Account verified successfully.',
                'token' => $token,
                'user' => $user,
            ], ResponseCode::SUCCESS);

        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'login' => 'required|string', 
                'password' => 'required|string',
            ]);

            $user = User::where('email', $request->login)
                        ->orWhere('username', $request->login)
                        ->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages(['login' => 'Invalid credentials.']);
            }

            if (!$user->is_active) {
                throw ValidationException::withMessages(['login' => 'Account is inactive. Verify OTP first.']);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => ResponseCode::SUCCESS,
                'token' => $token,
                'user' => $user,
                'roles' => $user->getRoleNames(),
            ], ResponseCode::SUCCESS);

        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json([
                'status' => ResponseCode::SUCCESS,
                'message' => 'Logged out successfully'
            ], ResponseCode::SUCCESS);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
