<?php

namespace App\Services;

use App\Models\Otp;
use App\Notifications\SendOtpNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
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
     * Generate an OTP and store it in both the database and cache.
     */
    public function generateAndStoreOtp(string $mobile, string $countryCode = '+91'): string
    {
        // Generate a random, cryptographically secure 4-digit OTP every time
        $otp = (string) random_int(1000, 9999);

        // Store OTP in database table `otps`
        try {
            Otp::create([
                'country_code' => $countryCode,
                'mobile' => $mobile,
                'otp' => $otp,
                'expires_at' => now()->addMinutes(10),
            ]);
        } catch (\Throwable $e) {
            // graceful fallback if DB fails
        }

        // Keep cache for speed and backward compatibility
        Cache::put("otp_{$countryCode}_{$mobile}", $otp, now()->addMinutes(10));
        Cache::put("otp_{$mobile}", $otp, now()->addMinutes(10));

        return $otp;
    }

    /**
     * Verify the supplied OTP against database records and cached entries.
     *
     * @throws ValidationException
     */
    public function verifyOtp(string $mobile, string $otp, string $countryCode = '+91'): bool
    {
        $cachedOtp = Cache::get("otp_{$countryCode}_{$mobile}") ?? Cache::get("otp_{$mobile}");

        /** @var Otp|null $dbOtp */
        $dbOtp = null;
        try {
            $dbOtp = Otp::where('mobile', $mobile)
                ->where(function ($q) use ($countryCode) {
                    $q->where('country_code', $countryCode)
                        ->orWhereNull('country_code');
                })
                ->where('expires_at', '>=', now())
                ->whereNull('verified_at')
                ->latest('id')
                ->first();
        } catch (\Throwable $e) {
            // fallback to cache
        }

        $isValid = false;
        if ($dbOtp && $dbOtp->otp === $otp) {
            $isValid = true;
        } elseif ($cachedOtp && $cachedOtp === $otp) {
            $isValid = true;
        }

        if (! $isValid) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid or expired OTP. Please try again.'],
            ]);
        }

        if ($dbOtp && ! $dbOtp->verified_at) {
            try {
                $dbOtp->update(['verified_at' => now()]);
            } catch (\Throwable $e) {
                // ignore
            }
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

    /**
     * Store pending user registration details before OTP verification.
     *
     * @param  array{name: string, email: string, role: string}  $profile
     */
    public function storePendingRegistration(string $mobile, string $countryCode, array $profile): void
    {
        Cache::put("pending_reg_{$countryCode}_{$mobile}", $profile, now()->addMinutes(15));
        Cache::put("pending_reg_{$mobile}", $profile, now()->addMinutes(15));
    }

    /**
     * Retrieve pending registration details.
     *
     * @return array{name: string, email: string, role: string}|null
     */
    public function getPendingRegistration(string $mobile, string $countryCode = '+91'): ?array
    {
        return Cache::get("pending_reg_{$countryCode}_{$mobile}") ?? Cache::get("pending_reg_{$mobile}");
    }

    /**
     * Forget pending registration details.
     */
    public function forgetPendingRegistration(string $mobile, string $countryCode = '+91'): void
    {
        Cache::forget("pending_reg_{$countryCode}_{$mobile}");
        Cache::forget("pending_reg_{$mobile}");
    }

    /**
     * Send an OTP via email notification.
     */
    public function sendOtpEmail(string $email, string $otp, ?string $userName = null): void
    {
        try {
            Notification::route('mail', $email)->notify(new SendOtpNotification($otp, $userName));
        } catch (\Throwable $e) {
            Log::warning('Could not send OTP email: '.$e->getMessage(), [
                'email' => $email,
            ]);
        }
    }

    /**
     * Mask an email for privacy display (e.g. h****t@gmail.com).
     */
    public function maskEmail(string $email): string
    {
        $parts = explode('@', $email, 2);
        if (count($parts) !== 2) {
            return $email;
        }

        $name = $parts[0];
        $domain = $parts[1];
        $len = strlen($name);

        if ($len <= 2) {
            $maskedName = substr($name, 0, 1).'*';
        } else {
            $maskedName = substr($name, 0, 1).str_repeat('*', min(4, $len - 2)).substr($name, -1);
        }

        return $maskedName.'@'.$domain;
    }
}
