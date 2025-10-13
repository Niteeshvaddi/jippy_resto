<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log;

class SocialLoginController extends Controller
{
    /**
     * Redirect to the social provider
     *
     * @param string $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToProvider($provider)
    {
        try {
            return Socialite::driver($provider)->redirect();
        } catch (\Exception $e) {
            Log::error('Social login redirect failed', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'ip' => request()->ip()
            ]);
            
            return redirect()->route('login')
                ->with('error', 'Social login is temporarily unavailable. Please try again later.');
        }
    }

    /**
     * Handle the callback from the social provider
     *
     * @param string $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleProviderCallback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
            
            // Log social login attempt
            Log::info('Social login callback received', [
                'provider' => $provider,
                'social_id' => $socialUser->getId(),
                'email' => $socialUser->getEmail(),
                'ip' => request()->ip()
            ]);

            // Check if user already exists
            $user = User::where('email', $socialUser->getEmail())->first();

            if ($user) {
                // Update existing user with social provider info
                $this->updateUserWithSocialInfo($user, $socialUser, $provider);
            } else {
                // Create new user
                $user = $this->createUserFromSocial($socialUser, $provider);
            }

            // Log the user in
            Auth::login($user, true);

            // Log successful social login
            Log::info('Social login successful', [
                'user_id' => $user->id,
                'provider' => $provider,
                'email' => $user->email,
                'ip' => request()->ip()
            ]);

            return redirect()->intended('/');

        } catch (\Exception $e) {
            Log::error('Social login callback failed', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'ip' => request()->ip()
            ]);

            return redirect()->route('login')
                ->with('error', 'Social login failed. Please try again or use email/password login.');
        }
    }

    /**
     * Create a new user from social provider data
     *
     * @param object $socialUser
     * @param string $provider
     * @return User
     */
    protected function createUserFromSocial($socialUser, $provider)
    {
        $user = User::create([
            'name' => $socialUser->getName() ?: $socialUser->getNickname() ?: 'User',
            'email' => $socialUser->getEmail(),
            'password' => Hash::make(Str::random(32)), // Random password for social users
            'email_verified_at' => now(), // Social providers verify emails
        ]);

        // Store social provider information
        $this->storeSocialProviderInfo($user, $socialUser, $provider);

        Log::info('New user created via social login', [
            'user_id' => $user->id,
            'provider' => $provider,
            'email' => $user->email,
            'name' => $user->name
        ]);

        return $user;
    }

    /**
     * Update existing user with social provider information
     *
     * @param User $user
     * @param object $socialUser
     * @param string $provider
     * @return void
     */
    protected function updateUserWithSocialInfo($user, $socialUser, $provider)
    {
        // Update name if not set or if social name is more complete
        if (empty($user->name) || $user->name === $user->email) {
            $user->name = $socialUser->getName() ?: $socialUser->getNickname() ?: $user->name;
        }

        // Mark email as verified if not already
        if (!$user->email_verified_at) {
            $user->email_verified_at = now();
        }

        $user->save();

        // Store social provider information
        $this->storeSocialProviderInfo($user, $socialUser, $provider);

        Log::info('Existing user updated with social info', [
            'user_id' => $user->id,
            'provider' => $provider,
            'email' => $user->email
        ]);
    }

    /**
     * Store social provider information
     * This could be stored in a separate table or as JSON in the user table
     *
     * @param User $user
     * @param object $socialUser
     * @param string $provider
     * @return void
     */
    protected function storeSocialProviderInfo($user, $socialUser, $provider)
    {
        // For now, we'll store this information in a simple way
        // You might want to create a separate social_providers table for more complex scenarios
        
        $socialData = [
            'provider' => $provider,
            'provider_id' => $socialUser->getId(),
            'avatar' => $socialUser->getAvatar(),
            'connected_at' => now()
        ];

        // Store in user's metadata or create a separate table
        // This is a simplified approach - you might want to create a proper social_providers table
        Log::info('Social provider info stored', [
            'user_id' => $user->id,
            'social_data' => $socialData
        ]);
    }

    /**
     * Get available social providers
     *
     * @return array
     */
    public static function getAvailableProviders()
    {
        return [
            'google' => [
                'name' => 'Google',
                'icon' => 'fab fa-google',
                'color' => '#4285F4'
            ],
            'facebook' => [
                'name' => 'Facebook',
                'icon' => 'fab fa-facebook-f',
                'color' => '#1877F2'
            ],
            'github' => [
                'name' => 'GitHub',
                'icon' => 'fab fa-github',
                'color' => '#333'
            ]
        ];
    }
}
