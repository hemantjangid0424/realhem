<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserAuthController extends Controller
{
    /**
     * Normalize a country dial code (e.g. +91, 91, +1, +971).
     */
    protected function normalizeCountryCode(?string $countryCode): string
    {
        if (empty($countryCode)) {
            return '+91';
        }

        $code = trim($countryCode);
        if (! str_starts_with($code, '+')) {
            $code = '+'.ltrim($code, '+');
        }

        return $code;
    }

    /**
     * Normalize a mobile number according to country code.
     */
    protected function normalizeMobile(string $mobile, string $countryCode = '+91'): string
    {
        $digits = preg_replace('/\D/', '', $mobile);

        if ($countryCode === '+91') {
            if (str_starts_with($digits, '91') && strlen($digits) === 12) {
                $digits = substr($digits, 2);
            } elseif (str_starts_with($digits, '0') && strlen($digits) === 11) {
                $digits = substr($digits, 1);
            }
        } else {
            // Strip international dial digits if user pasted full number including country code
            $codeDigits = ltrim($countryCode, '+');
            if (str_starts_with($digits, $codeDigits) && strlen($digits) > strlen($codeDigits) + 6) {
                $digits = substr($digits, strlen($codeDigits));
            }

            // Strip leading trunk zero (e.g. 050 -> 50 in UAE/UK)
            if (str_starts_with($digits, '0') && strlen($digits) > 8) {
                $digits = ltrim($digits, '0');
            }
        }

        return $digits;
    }

    /**
     * Send an OTP to the given mobile number.
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'country_code' => ['nullable', 'string', 'max:8'],
            'mobile' => ['required', 'string'],
        ]);

        $countryCode = $this->normalizeCountryCode($validated['country_code'] ?? '+91');
        $mobile = $this->normalizeMobile($validated['mobile'], $countryCode);

        // Validation based on country code
        if ($countryCode === '+91') {
            if (strlen($mobile) !== 10 || ! preg_match('/^[6-9]\d{9}$/', $mobile)) {
                throw ValidationException::withMessages([
                    'mobile' => ['Please enter a valid 10-digit Indian mobile number.'],
                ]);
            }
        } else {
            if (strlen($mobile) < 7 || strlen($mobile) > 15 || ! preg_match('/^\d{7,15}$/', $mobile)) {
                throw ValidationException::withMessages([
                    'mobile' => ['Please enter a valid mobile number (7 to 15 digits).'],
                ]);
            }
        }

        // For demo/development convenience, use standard 1234 or random 4 digits
        $otp = config('app.env') === 'production' ? (string) rand(1000, 9999) : '1234';

        // Store OTP in cache for 10 minutes
        Cache::put("otp_{$countryCode}_{$mobile}", $otp, now()->addMinutes(10));
        // Keep single mobile cache key for backward compatibility
        Cache::put("otp_{$mobile}", $otp, now()->addMinutes(10));

        $user = User::where(function ($query) use ($countryCode, $mobile) {
            $query->where('country_code', $countryCode)
                ->where('mobile', $mobile);
        })->orWhere(function ($query) use ($countryCode, $mobile) {
            if ($countryCode === '+91') {
                $query->whereNull('country_code')->where('mobile', $mobile);
            }
        })->first();

        $isRegistered = $user !== null && ! empty($user->name) && ! empty($user->email);

        return response()->json([
            'message' => "OTP has been sent to {$countryCode} {$mobile}",
            'country_code' => $countryCode,
            'mobile' => $mobile,
            'full_mobile' => "{$countryCode} {$mobile}",
            'is_registered' => $isRegistered,
            'dev_otp' => $otp, // Helpful for instant testing
        ]);
    }

    /**
     * Verify the entered OTP.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'country_code' => ['nullable', 'string', 'max:8'],
            'mobile' => ['required', 'string'],
            'otp' => ['required', 'string', 'min:4', 'max:6'],
        ]);

        $countryCode = $this->normalizeCountryCode($validated['country_code'] ?? '+91');
        $mobile = $this->normalizeMobile($validated['mobile'], $countryCode);
        $otp = trim($validated['otp']);

        $cachedOtp = Cache::get("otp_{$countryCode}_{$mobile}") ?? Cache::get("otp_{$mobile}");

        // Allow cached OTP or default demo OTP '1234'
        if ($otp !== '1234' && $otp !== $cachedOtp) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid or expired OTP. Please try again.'],
            ]);
        }

        Cache::forget("otp_{$countryCode}_{$mobile}");
        Cache::forget("otp_{$mobile}");

        /** @var User|null $user */
        $user = User::where(function ($query) use ($countryCode, $mobile) {
            $query->where('country_code', $countryCode)
                ->where('mobile', $mobile);
        })->orWhere(function ($query) use ($countryCode, $mobile) {
            if ($countryCode === '+91') {
                $query->whereNull('country_code')->where('mobile', $mobile);
            }
        })->first();

        // If user is already registered with name and email, log them in
        if ($user && ! empty($user->name) && ! empty($user->email)) {
            $token = $user->createToken('client-token')->plainTextToken;

            return response()->json([
                'status' => 'logged_in',
                'message' => 'Logged in successfully.',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'country_code' => $user->country_code ?? '+91',
                    'mobile' => $user->mobile,
                    'role' => $user->role,
                ],
            ]);
        }

        // Otherwise, new user needs to complete profile (name, email)
        $sessionToken = Str::random(40);
        Cache::put("verified_mobile_{$sessionToken}", [
            'country_code' => $countryCode,
            'mobile' => $mobile,
        ], now()->addMinutes(15));

        return response()->json([
            'status' => 'requires_profile',
            'message' => 'Mobile verified successfully. Please enter your name and email.',
            'session_token' => $sessionToken,
            'country_code' => $countryCode,
            'mobile' => $mobile,
            'full_mobile' => "{$countryCode} {$mobile}",
        ]);
    }

    /**
     * Complete registration profile for a newly verified mobile user.
     */
    public function completeProfile(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_token' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['nullable', 'string', 'in:user,owner,agent'],
        ]);

        $sessionToken = $validated['session_token'];
        $sessionData = Cache::get("verified_mobile_{$sessionToken}");

        if (! $sessionData) {
            throw ValidationException::withMessages([
                'session_token' => ['Verification session has expired. Please verify your mobile again.'],
            ]);
        }

        if (is_array($sessionData)) {
            $countryCode = $sessionData['country_code'] ?? '+91';
            $mobile = $sessionData['mobile'];
        } else {
            $countryCode = '+91';
            $mobile = (string) $sessionData;
        }

        $user = User::updateOrCreate(
            [
                'country_code' => $countryCode,
                'mobile' => $mobile,
            ],
            [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => $validated['role'] ?? 'user',
                'email_verified_at' => now(),
            ]
        );

        Cache::forget("verified_mobile_{$sessionToken}");

        $token = $user->createToken('client-token')->plainTextToken;

        return response()->json([
            'status' => 'logged_in',
            'message' => 'Registration complete! Welcome to RealHem.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'country_code' => $user->country_code,
                'mobile' => $user->mobile,
                'role' => $user->role,
            ],
        ]);
    }

    /**
     * Get the authenticated user's profile.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'country_code' => $user->country_code ?? '+91',
                'mobile' => $user->mobile,
                'role' => $user->role,
            ],
        ]);
    }

    /**
     * Update the authenticated user's profile.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role' => ['nullable', 'string', 'in:user,owner,agent,builder'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'] ?? $user->role,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'country_code' => $user->country_code ?? '+91',
                'mobile' => $user->mobile,
                'role' => $user->role,
            ],
        ]);
    }

    /**
     * Log out the authenticated user.
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user && method_exists($user, 'currentAccessToken') && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }
}
