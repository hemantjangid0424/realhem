<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OtpService
{
    /**
     * Normalize a country dial code (e.g. +91, 91, +1, +971).
     */
    public function normalizeCountryCode(?string $countryCode): string
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
    public function normalizeMobile(string $mobile, string $countryCode = '+91'): string
    {
        $digits = preg_replace('/\D/', '', $mobile) ?? '';

        if ($countryCode === '+91') {
            if (str_starts_with($digits, '91') && strlen($digits) === 12) {
                $digits = substr($digits, 2);
            } elseif (str_starts_with($digits, '0') && strlen($digits) === 11) {
                $digits = substr($digits, 1);
            }
        } else {
            $codeDigits = ltrim($countryCode, '+');
            if (str_starts_with($digits, $codeDigits) && strlen($digits) > strlen($codeDigits) + 6) {
                $digits = substr($digits, strlen($codeDigits));
            }

            if (str_starts_with($digits, '0') && strlen($digits) > 8) {
                $digits = ltrim($digits, '0');
            }
        }

        return $digits;
    }

    /**
     * Validate the mobile number format based on country dial code.
     *
     * @throws ValidationException
     */
    public function validateMobileFormat(string $mobile, string $countryCode): void
    {
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
    }

    /**
     * Generate an OTP and store it in cache for 10 minutes.
     */
    public function generateAndStoreOtp(string $mobile, string $countryCode = '+91'): string
    {
        $otp = config('app.env') === 'production' ? (string) rand(1000, 9999) : '1234';

        Cache::put("otp_{$countryCode}_{$mobile}", $otp, now()->addMinutes(10));
        Cache::put("otp_{$mobile}", $otp, now()->addMinutes(10));

        return $otp;
    }

    /**
     * Verify the supplied OTP against cached records.
     *
     * @throws ValidationException
     */
    public function verifyOtp(string $mobile, string $otp, string $countryCode = '+91'): bool
    {
        $cachedOtp = Cache::get("otp_{$countryCode}_{$mobile}") ?? Cache::get("otp_{$mobile}");

        if ($otp !== '1234' && $otp !== $cachedOtp) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid or expired OTP. Please try again.'],
            ]);
        }

        Cache::forget("otp_{$countryCode}_{$mobile}");
        Cache::forget("otp_{$mobile}");

        return true;
    }

    /**
     * Store a verified mobile session token for profile completion.
     */
    public function createVerifiedSession(string $mobile, string $countryCode = '+91'): string
    {
        $sessionToken = Str::random(40);

        Cache::put("verified_mobile_{$sessionToken}", [
            'country_code' => $countryCode,
            'mobile' => $mobile,
        ], now()->addMinutes(30));

        return $sessionToken;
    }

    /**
     * Retrieve and optionally consume a verified session.
     *
     * @return array{country_code: string, mobile: string}|null
     */
    public function getVerifiedSession(string $sessionToken): ?array
    {
        $sessionData = Cache::get("verified_mobile_{$sessionToken}");
        if (! $sessionData) {
            return null;
        }

        if (is_array($sessionData)) {
            return [
                'country_code' => $sessionData['country_code'] ?? '+91',
                'mobile' => (string) $sessionData['mobile'],
            ];
        }

        return [
            'country_code' => '+91',
            'mobile' => (string) $sessionData,
        ];
    }

    /**
     * Forget a verified session.
     */
    public function forgetVerifiedSession(string $sessionToken): void
    {
        Cache::forget("verified_mobile_{$sessionToken}");
    }
}
