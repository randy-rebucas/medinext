<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Setting;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class SettingsController extends BaseController
{
    /**
     * Get all settings
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $settings = Setting::where('clinic_id', $currentClinic->id)
                ->get()
                ->keyBy('key');

            return $this->successResponse([
                'settings' => $settings,
            ], 'Settings retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Update settings
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $validator = Validator::make($request->all(), [
                'settings' => 'required|array',
                'settings.*' => 'required|string',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $settings = $request->get('settings');

            foreach ($settings as $key => $value) {
                Setting::updateOrCreate(
                    [
                        'clinic_id' => $currentClinic->id,
                        'key' => $key,
                    ],
                    [
                        'value' => $value,
                    ]
                );
            }

            $updatedSettings = Setting::where('clinic_id', $currentClinic->id)
                ->get()
                ->keyBy('key');

            return $this->successResponse([
                'settings' => $updatedSettings,
            ], 'Settings updated successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get clinic settings
     */
    public function clinicSettings(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $clinicSettings = [
                'clinic' => $currentClinic,
                'settings' => $currentClinic->settings ?? [],
            ];

            return $this->successResponse($clinicSettings, 'Clinic settings retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Update clinic settings
     */
    public function updateClinicSettings(Request $request): JsonResponse
    {
        try {
            $currentClinic = $this->getCurrentClinic();

            if (!$currentClinic) {
                return $this->errorResponse('No clinic access', null, 403);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'timezone' => 'sometimes|required|string|max:50',
                'logo_url' => 'nullable|url|max:500',
                'address' => 'nullable|array',
                'address.street' => 'nullable|string|max:255',
                'address.city' => 'nullable|string|max:100',
                'address.state' => 'nullable|string|max:100',
                'address.zip' => 'nullable|string|max:20',
                'address.country' => 'nullable|string|max:100',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'website' => 'nullable|url|max:255',
                'description' => 'nullable|string|max:1000',
                'settings' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $currentClinic->update($validator->validated());

            return $this->successResponse([
                'clinic' => $currentClinic,
            ], 'Clinic settings updated successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get user settings
     */
    public function userSettings(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser();

            $userSettings = [
                'user' => $user,
                'preferences' => [
                    'theme' => 'light', // This would come from user preferences
                    'language' => 'en',
                    'timezone' => 'UTC',
                    'notifications' => [
                        'email' => true,
                        'push' => true,
                        'sms' => false,
                    ],
                ],
            ];

            return $this->successResponse($userSettings, 'User settings retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Update user settings
     */
    public function updateUserSettings(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser();

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|max:255|unique:users,email,' . $user->id,
                'phone' => 'nullable|string|max:20',
                'preferences' => 'nullable|array',
                'preferences.theme' => 'nullable|string|in:light,dark',
                'preferences.language' => 'nullable|string|in:en,es,fr,de',
                'preferences.timezone' => 'nullable|string|max:50',
                'preferences.notifications' => 'nullable|array',
                'preferences.notifications.email' => 'nullable|boolean',
                'preferences.notifications.push' => 'nullable|boolean',
                'preferences.notifications.sms' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $data = $validator->validated();

            // Update user basic info
            if (isset($data['name']) || isset($data['email']) || isset($data['phone'])) {
                $userData = array_intersect_key($data, array_flip(['name', 'email', 'phone']));
                $user->update($userData);
            }

            // Update user preferences (this would be stored in a user_preferences table in a real implementation)
            if (isset($data['preferences'])) {
                // For now, we'll just return the preferences
                // In a real implementation, you would store these in a separate table
            }

            return $this->successResponse([
                'user' => $user,
                'preferences' => $data['preferences'] ?? [],
            ], 'User settings updated successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
