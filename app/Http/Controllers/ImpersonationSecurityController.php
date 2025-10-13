<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Exception\AuthException;

class ImpersonationSecurityController extends Controller
{
    private $auth;
    private $firebase;
    private $allowedOrigins;
    private $allowedIPs;

    public function __construct()
    {
        // Initialize Firebase Admin SDK with enhanced security
        // Temporarily disabled to fix routing issues
        $this->firebase = null;
        $this->auth = null;
        
        // TODO: Fix Firebase configuration and re-enable
        /*
        try {
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
        } catch (\Exception $e) {
            Log::error('Firebase initialization failed', ['error' => $e->getMessage()]);
            // Continue without Firebase for now - we'll handle this gracefully
        }
        */
        
        // Security configurations
        $this->allowedOrigins = [
            'https://admin.restaurant-system.com',
            'https://restaurant.restaurant-system.com',
            'http://127.0.0.1:8000',
            'http://127.0.0.1:8001',
            'http://localhost:8000',
            'http://localhost:8001'
        ];
        
        $this->allowedIPs = [
            '127.0.0.1',
            '::1',
            // Add your production admin IPs here
            // '203.0.113.0/24', // Example IP range
        ];
    }

    /**
     * Enhanced security middleware for impersonation requests
     */
    public function validateSecurity(Request $request)
    {
        $clientIP = $this->getClientIP($request);
        $origin = $request->header('Origin') ?? $request->header('Referer');
        $userAgent = $request->header('User-Agent');
        
        // 1. IP Whitelist Check
        if (!$this->isIPAllowed($clientIP)) {
            $this->logSecurityEvent('ip_blocked', [
                'ip' => $clientIP,
                'origin' => $origin,
                'user_agent' => $userAgent
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Access denied from this IP address'
            ], 403);
        }

        // 2. Origin Validation
        if (!$this->isOriginAllowed($origin)) {
            $this->logSecurityEvent('origin_blocked', [
                'ip' => $clientIP,
                'origin' => $origin,
                'user_agent' => $userAgent
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Access denied from this origin'
            ], 403);
        }

        // 3. Rate Limiting with IP + User combination
        $rateLimitKey = $this->getRateLimitKey($request, $clientIP);
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) { // 5 attempts per 15 minutes
            $this->logSecurityEvent('rate_limit_exceeded', [
                'ip' => $clientIP,
                'key' => $rateLimitKey,
                'attempts' => RateLimiter::attempts($rateLimitKey)
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Too many attempts. Please try again later.',
                'retry_after' => RateLimiter::availableIn($rateLimitKey)
            ], 429);
        }

        // 4. CSRF Token Validation
        if (!$this->validateCSRFToken($request)) {
            $this->logSecurityEvent('csrf_failed', [
                'ip' => $clientIP,
                'origin' => $origin
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Invalid security token'
            ], 403);
        }

        // 5. Admin Session Validation
        if (!$this->validateAdminSession($request)) {
            $this->logSecurityEvent('invalid_admin_session', [
                'ip' => $clientIP,
                'admin_id' => $request->admin_id ?? 'unknown'
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Invalid admin session'
            ], 401);
        }

        return null; // All security checks passed
    }

    /**
     * Generate secure impersonation token with enhanced security
     */
    public function generateSecureImpersonationToken(Request $request)
    {
        try {
            // Run security validation
            $securityCheck = $this->validateSecurity($request);
            if ($securityCheck) {
                return $securityCheck;
            }

            // Enhanced input validation
            $validator = Validator::make($request->all(), [
                'restaurant_uid' => 'required|string|regex:/^[a-zA-Z0-9_-]+$/|max:128',
                'admin_id' => 'required|string|regex:/^[a-zA-Z0-9_-]+$/|max:128',
                'admin_email' => 'required|email|max:255',
                'admin_name' => 'required|string|max:255',
                'session_token' => 'required|string|size:64'
            ]);

            if ($validator->fails()) {
                $this->logSecurityEvent('validation_failed', [
                    'errors' => $validator->errors()->toArray(),
                    'ip' => $this->getClientIP($request)
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid request parameters',
                    'errors' => $validator->errors()
                ], 400);
            }

            $restaurantUid = $request->restaurant_uid;
            $adminId = $request->admin_id;
            $adminEmail = $request->admin_email;
            $adminName = $request->admin_name;
            $sessionToken = $request->session_token;

            // Verify admin session token
            if (!$this->verifyAdminSessionToken($adminId, $sessionToken)) {
                $this->logSecurityEvent('invalid_session_token', [
                    'admin_id' => $adminId,
                    'ip' => $this->getClientIP($request)
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid admin session'
                ], 401);
            }

            // Verify restaurant exists and is active
            if (!$this->verifyRestaurantExists($restaurantUid)) {
                $this->logSecurityEvent('restaurant_not_found', [
                    'restaurant_uid' => $restaurantUid,
                    'admin_id' => $adminId,
                    'ip' => $this->getClientIP($request)
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Restaurant not found or inactive'
                ], 404);
            }

            // Generate secure cache key with timestamp and random component
            $cacheKey = 'impersonation_' . hash('sha256', $adminId . $restaurantUid . time() . Str::random(32));
            
            // Check if Firebase is available
            if (!$this->auth) {
                return response()->json([
                    'success' => false,
                    'message' => 'Firebase service unavailable'
                ], 503);
            }

            // Create custom token with enhanced claims
            $customToken = $this->auth->createCustomToken($restaurantUid, [
                'impersonated_by' => $adminId,
                'impersonated_at' => time(),
                'cache_key' => $cacheKey,
                'expires_at' => time() + 300, // 5 minutes
                'admin_email' => $adminEmail,
                'admin_name' => $adminName,
                'ip_address' => $this->getClientIP($request),
                'user_agent_hash' => hash('sha256', $request->header('User-Agent')),
                'token_type' => 'impersonation',
                'version' => '2.0'
            ]);

            // Store comprehensive token info in cache
            $tokenInfo = [
                'restaurant_uid' => $restaurantUid,
                'admin_id' => $adminId,
                'admin_email' => $adminEmail,
                'admin_name' => $adminName,
                'generated_at' => time(),
                'expires_at' => time() + 300,
                'used' => false,
                'ip_address' => $this->getClientIP($request),
                'user_agent' => $request->header('User-Agent'),
                'session_token' => $sessionToken,
                'attempts' => 0,
                'max_attempts' => 3
            ];

            Cache::put($cacheKey, $tokenInfo, 300); // 5 minutes

            // Log successful token generation
            $this->logSecurityEvent('token_generated', [
                'admin_id' => $adminId,
                'restaurant_uid' => $restaurantUid,
                'cache_key' => $cacheKey,
                'ip' => $this->getClientIP($request)
            ]);

            // Create secure redirect URL with encrypted parameters
            $encryptedParams = $this->encryptRedirectParams([
                'token' => (string)$customToken,
                'uid' => $restaurantUid,
                'cache_key' => $cacheKey,
                'expires' => time() + 300
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Impersonation token generated successfully',
                'data' => [
                    'encrypted_params' => $encryptedParams,
                    'expires_in' => 300,
                    'redirect_url' => config('firebase.restaurant_panel_url') . '/login?impersonation=' . $encryptedParams
                ]
            ]);

        } catch (AuthException $e) {
            $this->logSecurityEvent('firebase_auth_error', [
                'error' => $e->getMessage(),
                'admin_id' => $request->admin_id ?? 'unknown',
                'ip' => $this->getClientIP($request)
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Authentication service error'
            ], 500);
        } catch (\Exception $e) {
            $this->logSecurityEvent('unexpected_error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $this->getClientIP($request)
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Validate impersonation token with enhanced security
     */
    public function validateSecureImpersonationToken(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'encrypted_params' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid request parameters'
                ], 400);
            }

            // Decrypt parameters
            $params = $this->decryptRedirectParams($request->encrypted_params);
            if (!$params) {
                $this->logSecurityEvent('decryption_failed', [
                    'ip' => $this->getClientIP($request)
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid token parameters'
                ], 401);
            }

            $token = $params['token'];
            $restaurantUid = $params['uid'];
            $cacheKey = $params['cache_key'];

            // Check if token has expired
            if (time() > $params['expires']) {
                $this->logSecurityEvent('token_expired', [
                    'cache_key' => $cacheKey,
                    'ip' => $this->getClientIP($request)
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Token has expired'
                ], 401);
            }

            // Check if token info exists in cache
            $tokenInfo = Cache::get($cacheKey);
            if (!$tokenInfo) {
                $this->logSecurityEvent('token_not_found', [
                    'cache_key' => $cacheKey,
                    'ip' => $this->getClientIP($request)
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Token not found or expired'
                ], 401);
            }

            // Check if token was already used
            if ($tokenInfo['used']) {
                $this->logSecurityEvent('token_already_used', [
                    'cache_key' => $cacheKey,
                    'admin_id' => $tokenInfo['admin_id'],
                    'ip' => $this->getClientIP($request)
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Token has already been used'
                ], 401);
            }

            // Check attempt limit
            if ($tokenInfo['attempts'] >= $tokenInfo['max_attempts']) {
                $this->logSecurityEvent('max_attempts_exceeded', [
                    'cache_key' => $cacheKey,
                    'attempts' => $tokenInfo['attempts'],
                    'ip' => $this->getClientIP($request)
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Maximum validation attempts exceeded'
                ], 401);
            }

            // Increment attempt counter
            $tokenInfo['attempts']++;
            Cache::put($cacheKey, $tokenInfo, 300);

            // Check if Firebase is available
            if (!$this->auth) {
                return response()->json([
                    'success' => false,
                    'message' => 'Firebase service unavailable'
                ], 503);
            }

            // Verify token with Firebase
            $verifiedToken = $this->auth->verifyIdToken($token);
            
            if ($verifiedToken->getClaim('uid') !== $restaurantUid) {
                $this->logSecurityEvent('uid_mismatch', [
                    'expected_uid' => $restaurantUid,
                    'actual_uid' => $verifiedToken->getClaim('uid'),
                    'cache_key' => $cacheKey,
                    'ip' => $this->getClientIP($request)
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Token UID mismatch'
                ], 401);
            }

            // Mark token as used
            $tokenInfo['used'] = true;
            $tokenInfo['used_at'] = time();
            $tokenInfo['used_ip'] = $this->getClientIP($request);
            Cache::put($cacheKey, $tokenInfo, 300);

            // Log successful validation
            $this->logSecurityEvent('token_validated', [
                'admin_id' => $tokenInfo['admin_id'],
                'restaurant_uid' => $restaurantUid,
                'cache_key' => $cacheKey,
                'ip' => $this->getClientIP($request)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Token validated successfully',
                'data' => [
                    'restaurant_uid' => $restaurantUid,
                    'admin_info' => [
                        'id' => $tokenInfo['admin_id'],
                        'email' => $tokenInfo['admin_email'],
                        'name' => $tokenInfo['admin_name']
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            $this->logSecurityEvent('validation_error', [
                'error' => $e->getMessage(),
                'ip' => $this->getClientIP($request)
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Token validation failed'
            ], 500);
        }
    }

    /**
     * Get client IP address with proxy support
     */
    private function getClientIP(Request $request)
    {
        $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $request->ip();
    }

    /**
     * Check if IP is allowed
     */
    private function isIPAllowed($ip)
    {
        if (app()->environment('local')) {
            return true; // Allow all IPs in local environment
        }

        foreach ($this->allowedIPs as $allowedIP) {
            if (strpos($allowedIP, '/') !== false) {
                // CIDR notation
                if ($this->ipInRange($ip, $allowedIP)) {
                    return true;
                }
            } else {
                // Exact match
                if ($ip === $allowedIP) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Check if IP is in CIDR range
     */
    private function ipInRange($ip, $range)
    {
        list($subnet, $bits) = explode('/', $range);
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $bits);
        $subnet &= $mask;
        return ($ip & $mask) == $subnet;
    }

    /**
     * Check if origin is allowed
     */
    private function isOriginAllowed($origin)
    {
        if (app()->environment('local')) {
            return true; // Allow all origins in local environment
        }

        if (!$origin) {
            return false;
        }

        $parsedOrigin = parse_url($origin);
        $originHost = $parsedOrigin['scheme'] . '://' . $parsedOrigin['host'];
        
        if (isset($parsedOrigin['port'])) {
            $originHost .= ':' . $parsedOrigin['port'];
        }

        return in_array($originHost, $this->allowedOrigins);
    }

    /**
     * Validate CSRF token
     */
    private function validateCSRFToken(Request $request)
    {
        $token = $request->header('X-CSRF-TOKEN') ?? $request->input('_token');
        return hash_equals(session()->token(), $token);
    }

    /**
     * Validate admin session
     */
    private function validateAdminSession(Request $request)
    {
        // Check if admin is authenticated
        if (!auth()->check()) {
            return false;
        }

        // Check if user has admin role
        $user = auth()->user();
        if (!$user || !$user->hasRole('admin')) {
            return false;
        }

        // Check session timeout (optional)
        $lastActivity = session('last_activity');
        if ($lastActivity && (time() - $lastActivity) > 3600) { // 1 hour timeout
            return false;
        }

        return true;
    }

    /**
     * Verify admin session token
     */
    private function verifyAdminSessionToken($adminId, $sessionToken)
    {
        $expectedToken = hash('sha256', $adminId . session()->getId() . config('app.key'));
        return hash_equals($expectedToken, $sessionToken);
    }

    /**
     * Verify restaurant exists and is active
     */
    private function verifyRestaurantExists($restaurantUid)
    {
        try {
            if (!$this->auth) {
                return false; // Firebase not available
            }
            $user = $this->auth->getUser($restaurantUid);
            return $user && !$user->disabled;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Encrypt redirect parameters
     */
    private function encryptRedirectParams($params)
    {
        $data = json_encode($params);
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', config('app.key'), 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt redirect parameters
     */
    private function decryptRedirectParams($encryptedParams)
    {
        try {
            $data = base64_decode($encryptedParams);
            $iv = substr($data, 0, 16);
            $encrypted = substr($data, 16);
            $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', config('app.key'), 0, $iv);
            return json_decode($decrypted, true);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get rate limit key
     */
    private function getRateLimitKey(Request $request, $ip)
    {
        $adminId = $request->admin_id ?? 'anonymous';
        return 'impersonation_rate_limit:' . $ip . ':' . $adminId;
    }

    /**
     * Log security events
     */
    private function logSecurityEvent($event, $data = [])
    {
        $logData = array_merge([
            'event' => $event,
            'timestamp' => time(),
            'date' => date('Y-m-d H:i:s'),
            'ip' => $this->getClientIP(request()),
            'user_agent' => request()->header('User-Agent'),
            'url' => request()->fullUrl()
        ], $data);

        Log::channel('security')->info('Impersonation Security Event', $logData);
        
        // Also store in database for audit trail
        try {
            \DB::table('security_audit_logs')->insert([
                'event_type' => $event,
                'data' => json_encode($logData),
                'ip_address' => $logData['ip'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (\Exception $e) {
            // Fallback to file logging if database fails
            Log::error('Failed to log security event to database', ['error' => $e->getMessage()]);
        }
    }
}
