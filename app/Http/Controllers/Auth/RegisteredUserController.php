<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => 'nullable|string|max:20',
            'role' => 'nullable|string|in:admin',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'is_active' => true,
        ]);

        // Start 14-day free trial
        $user->startTrial();

        // Always create a default clinic for individual users and assign admin role
        $adminRole = \App\Models\Role::where('name', 'admin')->first();

        if (!$adminRole) {
            // If admin role doesn't exist, this is a critical error
            Log::error('Admin role not found during user registration', [
                'user_id' => $user->id,
                'user_email' => $user->email
            ]);
            throw new \Exception('System configuration error: Admin role not found. Please contact support.');
        }

        // Create a default clinic for the user
        $defaultClinic = \App\Models\Clinic::create([
            'name' => $user->name . "'s Practice",
            'address' => 'To be updated',
            'phone' => $user->phone,
            'email' => $user->email,
            'is_active' => true,
        ]);

        // Assign user to clinic with admin role
        $user->clinics()->attach($defaultClinic->id, [
            'role_id' => $adminRole->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Log successful admin role assignment
        Log::info('User assigned admin role during registration', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'clinic_id' => $defaultClinic->id,
            'role_id' => $adminRole->id
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Redirect to onboarding wizard
        return redirect()->route('onboarding.welcome');
    }
}
