<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\User;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;

class AuthController extends BaseController
{
    /**
     * User login
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string|min:6',
                'clinic_id' => 'nullable|integer|exists:clinics,id',
                'remember' => 'boolean',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $credentials = $request->only('email', 'password');
            $remember = $request->boolean('remember', false);

            if (!Auth::attempt($credentials, $remember)) {
                return $this->errorResponse('Invalid credentials', null, 401);
            }

            $user = Auth::user();

            // Check if user is active
            if (!$user->is_active) {
                Auth::logout();
                return $this->errorResponse('Account is deactivated', null, 401);
            }

            // Check clinic access if clinic_id is provided
            if ($request->has('clinic_id')) {
                if (!$user->clinics()->where('clinic_id', $request->clinic_id)->exists()) {
                    Auth::logout();
                    return $this->errorResponse('No access to specified clinic', null, 403);
                }
            }

            // Create token
            $token = $user->createToken('api-token', ['*'], now()->addDays(30))->plainTextToken;

            // Get user's clinics and roles
            $user->load(['clinics', 'roles']);

            return $this->successResponse([
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_at' => now()->addDays(30)->toISOString(),
                'clinic_access' => $user->clinics->map(function ($clinic) {
                    return [
                        'id' => $clinic->id,
                        'name' => $clinic->name,
                        'slug' => $clinic->slug,
                        'roles' => $clinic->pivot->role_id ? [$clinic->pivot->role_id] : []
                    ];
                }),
            ], 'Login successful');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * User registration
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'phone' => 'nullable|string|max:20',
                'clinic_id' => 'required|integer|exists:clinics,id',
                'role_id' => 'nullable|integer|exists:roles,id',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'is_active' => true,
            ]);

            // Assign clinic and role
            if ($request->clinic_id) {
                $user->clinics()->attach($request->clinic_id, [
                    'role_id' => $request->role_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Fire registered event
            event(new Registered($user));

            // Create token
            $token = $user->createToken('api-token', ['*'], now()->addDays(30))->plainTextToken;

            $user->load(['clinics', 'roles']);

            return $this->successResponse([
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_at' => now()->addDays(30)->toISOString(),
            ], 'Registration successful', 201);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * User logout
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return $this->successResponse(null, 'Logout successful');
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Delete current token
            $request->user()->currentAccessToken()->delete();
            
            // Create new token
            $token = $user->createToken('api-token', ['*'], now()->addDays(30))->plainTextToken;

            return $this->successResponse([
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_at' => now()->addDays(30)->toISOString(),
            ], 'Token refreshed successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $user->load(['clinics', 'roles']);

            return $this->successResponse([
                'user' => $user,
                'clinic_access' => $user->clinics->map(function ($clinic) {
                    return [
                        'id' => $clinic->id,
                        'name' => $clinic->name,
                        'slug' => $clinic->slug,
                        'roles' => $clinic->pivot->role_id ? [$clinic->pivot->role_id] : []
                    ];
                }),
            ], 'User information retrieved');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
                'phone' => 'nullable|string|max:20',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $user->update($request->only(['name', 'email', 'phone']));

            return $this->successResponse([
                'user' => $user->fresh(),
            ], 'Profile updated successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'password' => 'required|string|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $user = $request->user();

            if (!Hash::check($request->current_password, $user->password)) {
                return $this->errorResponse('Current password is incorrect', null, 422);
            }

            $user->update([
                'password' => Hash::make($request->password),
            ]);

            return $this->successResponse(null, 'Password updated successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Forgot password
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $status = Password::sendResetLink($request->only('email'));

            if ($status === Password::RESET_LINK_SENT) {
                return $this->successResponse(null, 'Password reset link sent to your email');
            }

            return $this->errorResponse('Unable to send reset link', null, 500);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'token' => 'required|string',
                'email' => 'required|email|exists:users,email',
                'password' => 'required|string|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function (User $user, string $password) {
                    $user->forceFill([
                        'password' => Hash::make($password),
                        'remember_token' => Str::random(60),
                    ])->save();

                    event(new PasswordReset($user));
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return $this->successResponse(null, 'Password reset successfully');
            }

            return $this->errorResponse('Unable to reset password', null, 500);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Verify email
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|exists:users,id',
                'hash' => 'required|string',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $user = User::findOrFail($request->id);

            if (!hash_equals((string) $request->hash, sha1($user->getEmailForVerification()))) {
                return $this->errorResponse('Invalid verification link', null, 400);
            }

            if ($user->hasVerifiedEmail()) {
                return $this->successResponse(null, 'Email already verified');
            }

            $user->markEmailAsVerified();

            return $this->successResponse(null, 'Email verified successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
