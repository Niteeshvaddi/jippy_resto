<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;

class AdminImpersonationController extends Controller
{
    private $auth;
    private $firebase;

    public function __construct()
    {
        // Initialize Firebase Admin SDK
        // Temporarily disabled to fix routing issues
        $this->firebase = null;
        $this->auth = null;
        
        // TODO: Fix Firebase configuration and re-enable
        /*
        $this->firebase = (new Factory)
            ->withServiceAccount([
                "type" => "service_account",
                "project_id" => config('firebase.project_id'),
                "private_key_id" => config('firebase.private_key_id'),
                "private_key" => config('firebase.private_key'),
                "client_email" => config('firebase.client_email'),
                "client_id" => config('firebase.client_id'),
                "auth_uri" => "https://accounts.google.com/o/oauth2/auth",
                "token_uri" => "https://oauth2.googleapis.com/token",
                "auth_provider_x509_cert_url" => "https://www.googleapis.com/oauth2/v1/certs",
                "client_x509_cert_url" => config('firebase.client_x509_cert_url')
            ])
            ->create();

        $this->auth = $this->firebase->getAuth();
        */
    }

    /**
     * Generate impersonation token for restaurant owner
     */
    public function generateImpersonationToken(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'restaurant_uid' => 'required|string',
                'admin_id' => 'required|string',
                'admin_email' => 'required|email'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid request parameters',
                    'errors' => $validator->errors()
                ], 400);
            }

            $restaurantUid = $request->restaurant_uid;
            $adminId = $request->admin_id;
            $adminEmail = $request->admin_email;

            // Rate limiting check
            $rateLimitKey = "impersonation_rate_limit_{$adminId}";
            $attempts = Cache::get($rateLimitKey, 0);
            
            if ($attempts >= 10) { // 10 attempts per hour
                return response()->json([
                    'success' => false,
                    'message' => 'Rate limit exceeded. Maximum 10 impersonation attempts per hour.'
                ], 429);
            }

            // Increment rate limit counter
            Cache::put($rateLimitKey, $attempts + 1, 3600); // 1 hour

            // Generate cache key for token validation
            $cacheKey = 'impersonation_token_' . uniqid();
            
            // Create custom token with 5-minute expiration
            $customToken = $this->auth->createCustomToken($restaurantUid, [
                'impersonated_by' => $adminId,
                'impersonated_at' => time(),
                'cache_key' => $cacheKey,
                'expires_at' => time() + 300 // 5 minutes
            ]);

            // Store token info in cache for validation
            Cache::put($cacheKey, [
                'restaurant_uid' => $restaurantUid,
                'admin_id' => $adminId,
                'admin_email' => $adminEmail,
                'generated_at' => time(),
                'used' => false
            ], 300); // 5 minutes

            // Log impersonation attempt
            Log::info('Admin impersonation token generated', [
                'admin_id' => $adminId,
                'admin_email' => $adminEmail,
                'restaurant_uid' => $restaurantUid,
                'cache_key' => $cacheKey,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Impersonation token generated successfully',
                'data' => [
                    'impersonation_token' => $customToken->toString(),
                    'restaurant_uid' => $restaurantUid,
                    'cache_key' => $cacheKey,
                    'expires_in' => 300,
                    'redirect_url' => config('app.restaurant_panel_url') . '/login?' . http_build_query([
                        'impersonation_token' => $customToken->toString(),
                        'restaurant_uid' => $restaurantUid,
                        'cache_key' => $cacheKey,
                        'auto_login' => 'true'
                    ])
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to generate impersonation token', [
                'error' => $e->getMessage(),
                'admin_id' => $request->admin_id ?? 'unknown',
                'restaurant_uid' => $request->restaurant_uid ?? 'unknown'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate impersonation token: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate impersonation token
     */
    public function validateImpersonationToken(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'impersonation_token' => 'required|string',
                'restaurant_uid' => 'required|string',
                'cache_key' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid request parameters'
                ], 400);
            }

            $token = $request->impersonation_token;
            $restaurantUid = $request->restaurant_uid;
            $cacheKey = $request->cache_key;

            // Check if token info exists in cache
            $tokenInfo = Cache::get($cacheKey);
            
            if (!$tokenInfo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token expired or invalid'
                ], 401);
            }

            // Check if token was already used
            if ($tokenInfo['used']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token has already been used'
                ], 401);
            }

            // Verify token with Firebase
            $verifiedToken = $this->auth->verifyIdToken($token);
            
            if ($verifiedToken->getClaim('uid') !== $restaurantUid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token UID mismatch'
                ], 401);
            }

            // Mark token as used
            $tokenInfo['used'] = true;
            Cache::put($cacheKey, $tokenInfo, 300);

            // Log successful validation
            Log::info('Impersonation token validated successfully', [
                'restaurant_uid' => $restaurantUid,
                'admin_id' => $tokenInfo['admin_id'],
                'cache_key' => $cacheKey
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Token validated successfully',
                'data' => [
                    'restaurant_uid' => $restaurantUid,
                    'admin_id' => $tokenInfo['admin_id'],
                    'admin_email' => $tokenInfo['admin_email']
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to validate impersonation token', [
                'error' => $e->getMessage(),
                'cache_key' => $request->cache_key ?? 'unknown'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Token validation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get impersonation statistics
     */
    public function getImpersonationStats(Request $request)
    {
        try {
            $adminId = $request->admin_id;
            
            if (!$adminId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin ID is required'
                ], 400);
            }

            $rateLimitKey = "impersonation_rate_limit_{$adminId}";
            $attempts = Cache::get($rateLimitKey, 0);
            $remaining = max(0, 10 - $attempts);

            return response()->json([
                'success' => true,
                'data' => [
                    'attempts_used' => $attempts,
                    'attempts_remaining' => $remaining,
                    'rate_limit_reset' => Cache::get($rateLimitKey . '_expires', time() + 3600)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics: ' . $e->getMessage()
            ], 500);
        }
    }
}
