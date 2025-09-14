<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Display the user's profile settings
     */
    public function profile()
    {
        return view('settings.profile');
    }

    /**
     * Update the user's profile
     */
    public function updateProfile(Request $request)
    {
        try {
            $this->logWebRequest('Update Profile', ['action' => 'updateProfile']);
            
            $user = $request->user();
            
            if (!$user) {
                $this->logSecurityEvent('Unauthenticated profile update attempt');
                return back()->with('error', 'User not authenticated.');
            }

            $rules = [
                'name' => 'required|string|max:255|regex:/^[a-zA-Z\s\-\'\.]+$/',
                'email' => 'required|email:rfc,dns|unique:users,email,' . $user->id,
            ];

            $messages = [
                'name.regex' => 'Name can only contain letters, spaces, hyphens, apostrophes, and periods.',
                'email.email' => 'Please provide a valid email address.',
            ];

            $validated = $this->validateAndSanitize($request, $rules, $messages);

            $user->update($validated);

            $this->logWebRequest('Profile Updated Successfully', ['user_id' => $user->id]);
            return back()->with('success', 'Profile updated successfully.');

        } catch (\Exception $e) {
            $this->handleException($e, 'SettingsController::updateProfile');
            return back()->with('error', 'Failed to update profile. Please try again.');
        }
    }
}
