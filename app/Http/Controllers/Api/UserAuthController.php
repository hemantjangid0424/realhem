<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CompleteProfileRequest;
use App\Http\Requests\Auth\SendOtpRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Requests\Auth\UploadAvatarRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserAuthController extends Controller
{
    /**
     * Send an OTP to the given mobile number and email.
     */
    public function sendOtp(SendOtpRequest $request, OtpService $otpService): JsonResponse
    {
        $countryCode = $otpService->normalizeCountryCode($request->validated('country_code'));
        $mobile = $otpService->normalizeMobile((string) $request->validated('mobile'), $countryCode);

        $otpService->validateMobileFormat($mobile, $countryCode);

        /** @var User|null $user */
        $user = User::where(function ($query) use ($countryCode, $mobile) {
            $query->where('country_code', $countryCode)
                ->where('mobile', $mobile);
        })->orWhere(function ($query) use ($mobile) {
            $query->where('mobile', $mobile);
        })->first();

        $isRegistered = (bool) $user;
        $name = trim((string) ($request->validated('name') ?? ''));
        $email = trim((string) ($request->validated('email') ?? ''));
        $role = (string) ($request->validated('role') ?? 'user');

        // If user is not registered and has not yet submitted name & email (Step 1 of new user)
        if (! $isRegistered && (empty($email) || empty($name))) {
            return response()->json([
                'status' => 'requires_profile',
                'next_step' => 'profile',
                'message' => 'Please provide your name, email, and role to continue.',
                'country_code' => $countryCode,
                'mobile' => $mobile,
                'full_mobile' => "{$countryCode} {$mobile}",
                'is_registered' => false,
            ]);
        }

        if (! $isRegistered) {
            // Check if email already belongs to another user
            $existingEmailUser = User::where('email', $email)->first();
            if ($existingEmailUser) {
                throw ValidationException::withMessages([
                    'email' => ['This email address is already associated with another account.'],
                ]);
            }

            // Save pending registration details in cache
            $otpService->storePendingRegistration($mobile, $countryCode, [
                'name' => $name,
                'email' => $email,
                'role' => in_array($role, ['user', 'owner', 'agent'], true) ? $role : 'user',
            ]);
        }

        // Generate unified OTP in DB and cache
        $otp = $otpService->generateAndStoreOtp($mobile, $countryCode);

        // Determine destination email
        $targetEmail = $isRegistered ? $user->email : $email;
        $targetName = $isRegistered ? $user->name : $name;

        if ($targetEmail) {
            $otpService->sendOtpEmail($targetEmail, $otp, $targetName);
        }

        $emailHint = $isRegistered
            ? ($targetEmail ? $otpService->maskEmail($targetEmail) : null)
            : $targetEmail;

        $message = $isRegistered
            ? "Verification code sent to {$countryCode} {$mobile}".($emailHint ? " and {$emailHint}" : '')
            : "Verification code sent to {$countryCode} {$mobile} and {$targetEmail}";

        return response()->json([
            'status' => 'otp_sent',
            'next_step' => 'otp',
            'message' => $message,
            'country_code' => $countryCode,
            'mobile' => $mobile,
            'full_mobile' => "{$countryCode} {$mobile}",
            'is_registered' => $isRegistered,
            'email_hint' => $emailHint,
            'dev_otp' => $otp,
        ]);
    }

    /**
     * Verify the entered OTP and authenticate user or complete registration.
     */
    public function verifyOtp(VerifyOtpRequest $request, OtpService $otpService): JsonResponse
    {
        $countryCode = $otpService->normalizeCountryCode($request->validated('country_code'));
        $mobile = $otpService->normalizeMobile((string) $request->validated('mobile'), $countryCode);

        $otpService->verifyOtp($mobile, trim((string) $request->validated('otp')), $countryCode);

        /** @var User|null $user */
        $user = User::where(function ($query) use ($countryCode, $mobile) {
            $query->where('country_code', $countryCode)
                ->where('mobile', $mobile);
        })->orWhere(function ($query) use ($mobile) {
            $query->where('mobile', $mobile);
        })->first();

        if ($user) {
            if (empty($user->country_code)) {
                $user->update(['country_code' => $countryCode]);
            }

            $token = $user->createToken('client-token')->plainTextToken;

            return response()->json([
                'status' => 'logged_in',
                'message' => 'Logged in successfully.',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'country_code' => $user->country_code ?? $countryCode,
                    'mobile' => $user->mobile,
                    'role' => $user->role,
                    'avatar' => $user->avatar,
                    'email_verified' => $user->hasVerifiedEmail(),
                ],
            ]);
        }

        // New user: check for pending registration data
        $pendingProfile = $otpService->getPendingRegistration($mobile, $countryCode);

        if (! $pendingProfile) {
            // Fallback for legacy profile completion
            $sessionToken = $otpService->createVerifiedSession($mobile, $countryCode);

            return response()->json([
                'status' => 'requires_profile',
                'message' => 'OTP verified. Please complete your profile to register.',
                'session_token' => $sessionToken,
                'mobile' => $mobile,
                'country_code' => $countryCode,
            ]);
        }

        // Create new user account automatically!
        $user = User::create([
            'name' => $pendingProfile['name'],
            'email' => $pendingProfile['email'],
            'country_code' => $countryCode,
            'mobile' => $mobile,
            'role' => $pendingProfile['role'] ?? 'user',
            'password' => bcrypt(Str::random(32)),
            'email_verified_at' => now(),
        ]);

        $otpService->forgetPendingRegistration($mobile, $countryCode);

        $token = $user->createToken('client-token')->plainTextToken;

        return response()->json([
            'status' => 'logged_in',
            'message' => 'Account created and logged in successfully.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'country_code' => $user->country_code ?? $countryCode,
                'mobile' => $user->mobile,
                'role' => $user->role,
                'avatar' => $user->avatar,
                'email_verified' => true,
            ],
        ], 201);
    }

    /**
     * Complete registration after OTP verification.
     */
    public function completeProfile(CompleteProfileRequest $request, OtpService $otpService): JsonResponse
    {
        $sessionToken = (string) $request->validated('session_token');
        $sessionData = $otpService->getVerifiedSession($sessionToken);

        if (! $sessionData) {
            throw ValidationException::withMessages([
                'session_token' => ['Verification session has expired. Please verify your mobile again.'],
            ]);
        }

        $countryCode = $sessionData['country_code'];
        $mobile = $sessionData['mobile'];

        $user = User::updateOrCreate(
            [
                'country_code' => $countryCode,
                'mobile' => $mobile,
            ],
            [
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'role' => $request->validated('role') ?? 'user',
                'email_verified_at' => null,
            ]
        );

        $otpService->forgetVerifiedSession($sessionToken);

        try {
            $user->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            Log::warning('Could not send verification email notification: '.$e->getMessage());
        }

        $token = $user->createToken('client-token')->plainTextToken;

        return response()->json([
            'status' => 'logged_in',
            'message' => 'Registration complete! A verification link has been sent to your email address.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'country_code' => $user->country_code,
                'mobile' => $user->mobile,
                'role' => $user->role,
                'avatar' => $user->avatar,
                'email_verified' => $user->hasVerifiedEmail(),
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
                'avatar' => $user->avatar,
            ],
        ]);
    }

    /**
     * Update the authenticated user's profile.
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        $user->update([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'role' => $request->validated('role') ?? $user->role,
            'avatar' => $request->has('avatar') ? $request->validated('avatar') : $user->avatar,
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
                'avatar' => $user->avatar,
            ],
        ]);
    }

    /**
     * Upload or update the authenticated user's profile picture.
     */
    public function uploadAvatar(UploadAvatarRequest $request): JsonResponse
    {
        $user = $request->user();
        $file = $request->file('avatar');

        // Delete previous avatar file if stored on public disk
        if ($user->avatar && str_contains($user->avatar, '/storage/avatars/')) {
            $oldPath = 'avatars/'.basename($user->avatar);
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $filename = 'avatar_user_'.$user->id.'_'.Str::random(10).'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('avatars', $filename, 'public');
        $avatarUrl = Storage::url($path);

        $user->update(['avatar' => $avatarUrl]);

        return response()->json([
            'status' => 'success',
            'message' => 'Profile picture updated successfully.',
            'avatar' => $avatarUrl,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'country_code' => $user->country_code ?? '+91',
                'mobile' => $user->mobile,
                'role' => $user->role,
                'avatar' => $user->avatar,
            ],
        ]);
    }

    /**
     * Delete the authenticated user's profile picture.
     */
    public function deleteAvatar(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->avatar && str_contains($user->avatar, '/storage/avatars/')) {
            $oldPath = 'avatars/'.basename($user->avatar);
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $user->update(['avatar' => null]);

        return response()->json([
            'status' => 'success',
            'message' => 'Profile picture removed successfully.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'country_code' => $user->country_code ?? '+91',
                'mobile' => $user->mobile,
                'role' => $user->role,
                'avatar' => null,
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

    /**
     * Mark the user's email address as verified from signed verification URL.
     */
    public function verifyEmail(Request $request, int|string $id, string $hash): JsonResponse|RedirectResponse
    {
        $user = User::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid email verification link or security hash.',
                ], 403);
            }

            $frontendUrl = env('NUXT_PUBLIC_SITE_URL', 'http://localhost:3000');

            return redirect("{$frontendUrl}/verify-email?status=invalid");
        }

        if ($user->hasVerifiedEmail()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'already_verified',
                    'message' => 'Email address is already verified.',
                ]);
            }

            $frontendUrl = env('NUXT_PUBLIC_SITE_URL', 'http://localhost:3000');

            return redirect("{$frontendUrl}/verify-email?status=already_verified");
        }

        $user->markEmailAsVerified();

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'verified',
                'message' => 'Email verified successfully! Your account is now fully verified.',
                'email_verified_at' => $user->email_verified_at,
            ]);
        }

        $frontendUrl = env('NUXT_PUBLIC_SITE_URL', 'http://localhost:3000');

        return redirect("{$frontendUrl}/verify-email?status=success");
    }

    /**
     * Resend verification email to authenticated user.
     */
    public function resendVerificationEmail(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'status' => 'unauthenticated',
                'message' => 'You must be logged in to resend email verification.',
            ], 401);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'status' => 'already_verified',
                'message' => 'Your email address is already verified.',
            ]);
        }

        try {
            $user->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            Log::warning('Could not resend verification email: '.$e->getMessage());
        }

        return response()->json([
            'status' => 'sent',
            'message' => 'Verification email sent successfully! Please check your inbox.',
        ]);
    }

    /**
     * Check email verification status for authenticated user.
     */
    public function emailVerificationStatus(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'email' => $user->email,
            'is_verified' => $user->hasVerifiedEmail(),
            'email_verified_at' => $user->email_verified_at,
        ]);
    }
}
