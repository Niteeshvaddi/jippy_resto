<?php
/**
 * Debug Impersonation System
 * 
 * This script helps debug the impersonation system by checking cache data
 * and providing a way to manually test the impersonation flow.
 */

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 Impersonation System Debug Tool\n";
echo "==================================\n\n";

// Get the impersonation key from command line or use a test key
$impersonationKey = $argv[1] ?? 'impersonation_b4c04bca8e6bf5647c9e79f91db085aa4887c229e7d5af92c37d6dae5997aa14';

echo "Checking impersonation key: {$impersonationKey}\n\n";

// Check cache data
$cacheData = Cache::get($impersonationKey);

if ($cacheData) {
    echo "✅ Cache data found:\n";
    echo json_encode($cacheData, JSON_PRETTY_PRINT) . "\n\n";
    
    // Check if data has required fields
    $requiredFields = ['restaurant_uid', 'token', 'expires_at'];
    $missingFields = [];
    
    foreach ($requiredFields as $field) {
        if (!isset($cacheData[$field])) {
            $missingFields[] = $field;
        }
    }
    
    if (empty($missingFields)) {
        echo "✅ All required fields present\n";
        
        // Check expiration
        if (time() > $cacheData['expires_at']) {
            echo "❌ Token has expired\n";
        } else {
            echo "✅ Token is still valid\n";
        }
    } else {
        echo "❌ Missing required fields: " . implode(', ', $missingFields) . "\n";
    }
} else {
    echo "❌ No cache data found for key: {$impersonationKey}\n";
}

echo "\n";

// List all cache keys that start with 'impersonation'
echo "🔍 All impersonation cache keys:\n";
$allKeys = Cache::getRedis()->keys('*impersonation*');
foreach ($allKeys as $key) {
    $key = str_replace(config('cache.prefix') . ':', '', $key);
    echo "- {$key}\n";
}

echo "\n";

// Test API endpoint
echo "🧪 Testing API endpoint...\n";
$url = "http://127.0.0.1:8001/api/check-impersonation?impersonation_key=" . urlencode($impersonationKey);
$response = file_get_contents($url);
$data = json_decode($response, true);

if ($data) {
    echo "API Response:\n";
    echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
} else {
    echo "❌ Failed to get API response\n";
}

echo "\n";

// Instructions
echo "📝 Instructions:\n";
echo "1. If no cache data is found, the admin panel is not storing data properly\n";
echo "2. If cache data is missing fields, the data structure is incorrect\n";
echo "3. If token is expired, generate a new one from the admin panel\n";
echo "4. Check the Laravel logs for more details: tail -f storage/logs/laravel.log\n";

echo "\n";
echo "🔧 To fix the issue:\n";
echo "1. Make sure the admin panel is calling the correct API endpoint\n";
echo "2. Check that the admin panel is storing data in the correct format\n";
echo "3. Verify that both admin panel and restaurant panel are using the same cache system\n";
echo "4. Check the Firebase configuration and service account setup\n";
