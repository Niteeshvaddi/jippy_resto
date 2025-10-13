# Admin Panel Integration Guide

## Overview

This guide shows how to integrate the impersonation feature into your admin panel. Since this codebase is the restaurant panel, you'll need to implement the admin-side functionality in your separate admin panel.

## Admin Panel Implementation

### 1. Add Impersonation Button to Restaurant List

```html
<!-- In your admin panel restaurant list table -->
<table class="table">
    <thead>
        <tr>
            <th>Restaurant Name</th>
            <th>Owner Email</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($restaurants as $restaurant)
        <tr>
            <td>{{ $restaurant->name }}</td>
            <td>{{ $restaurant->owner_email }}</td>
            <td>{{ $restaurant->status }}</td>
            <td>
                <button class="btn btn-sm btn-primary" 
                        onclick="impersonateRestaurant('{{ $restaurant->firebase_uid }}', '{{ $restaurant->name }}')">
                    <i class="fa fa-user-secret"></i> Impersonate
                </button>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
```

### 2. JavaScript Implementation

```javascript
// Add this to your admin panel's JavaScript
function impersonateRestaurant(restaurantUid, restaurantName) {
    // Show confirmation dialog
    if (!confirm(`Are you sure you want to impersonate ${restaurantName}?`)) {
        return;
    }
    
    // Show loading state
    showImpersonationLoading();
    
    // Prepare request data
    const requestData = {
        restaurant_uid: restaurantUid,
        admin_id: getCurrentAdminId(), // Implement this function
        admin_email: getCurrentAdminEmail() // Implement this function
    };
    
    // Make API call to restaurant panel
    fetch('https://restaurant.jippymart.in/admin/impersonate/generate-token', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken() // Implement this function
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(data => {
        hideImpersonationLoading();
        
        if (data.success) {
            // Show success message
            showSuccessMessage(`Impersonation token generated for ${restaurantName}`);
            
            // Open restaurant panel in new tab
            window.open(data.data.redirect_url, '_blank');
            
            // Log the action
            logImpersonationAction(restaurantUid, restaurantName, 'success');
        } else {
            // Show error message
            showErrorMessage('Failed to generate impersonation token: ' + data.message);
            
            // Log the error
            logImpersonationAction(restaurantUid, restaurantName, 'error', data.message);
        }
    })
    .catch(error => {
        hideImpersonationLoading();
        showErrorMessage('Network error: ' + error.message);
        logImpersonationAction(restaurantUid, restaurantName, 'network_error', error.message);
    });
}

// Helper functions
function getCurrentAdminId() {
    // Return current admin's ID from session/localStorage
    return window.currentAdminId || 'admin_' + Date.now();
}

function getCurrentAdminEmail() {
    // Return current admin's email from session/localStorage
    return window.currentAdminEmail || 'admin@jippymart.in';
}

function getCsrfToken() {
    // Return CSRF token from meta tag or form
    return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
}

function showImpersonationLoading() {
    // Show loading indicator
    const loadingDiv = document.createElement('div');
    loadingDiv.id = 'impersonation-loading';
    loadingDiv.innerHTML = `
        <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; justify-content: center; align-items: center;">
            <div style="background: white; padding: 20px; border-radius: 5px; text-align: center;">
                <div class="spinner-border" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-2">Generating impersonation token...</p>
            </div>
        </div>
    `;
    document.body.appendChild(loadingDiv);
}

function hideImpersonationLoading() {
    const loadingDiv = document.getElementById('impersonation-loading');
    if (loadingDiv) {
        loadingDiv.remove();
    }
}

function showSuccessMessage(message) {
    // Show success toast/alert
    alert('Success: ' + message); // Replace with your toast system
}

function showErrorMessage(message) {
    // Show error toast/alert
    alert('Error: ' + message); // Replace with your toast system
}

function logImpersonationAction(restaurantUid, restaurantName, status, error = null) {
    // Log the impersonation action
    console.log('Impersonation Action:', {
        restaurant_uid: restaurantUid,
        restaurant_name: restaurantName,
        status: status,
        error: error,
        timestamp: new Date().toISOString(),
        admin_id: getCurrentAdminId()
    });
    
    // You can also send this to your analytics/logging service
    // sendToAnalytics('impersonation_attempt', { ... });
}
```

### 3. Rate Limiting Display

```javascript
// Check and display rate limiting status
function checkImpersonationStats() {
    const adminId = getCurrentAdminId();
    
    fetch(`https://restaurant.jippymart.in/admin/impersonate/stats?admin_id=${adminId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateRateLimitDisplay(data.data);
        }
    })
    .catch(error => {
        console.error('Failed to get impersonation stats:', error);
    });
}

function updateRateLimitDisplay(stats) {
    const rateLimitDiv = document.getElementById('rate-limit-display');
    if (rateLimitDiv) {
        rateLimitDiv.innerHTML = `
            <small class="text-muted">
                Impersonation attempts: ${stats.attempts_used}/10 remaining
                ${stats.attempts_remaining === 0 ? ' (Rate limited)' : ''}
            </small>
        `;
    }
}

// Call this when the page loads
document.addEventListener('DOMContentLoaded', function() {
    checkImpersonationStats();
});
```

### 4. CSS Styling

```css
/* Add to your admin panel CSS */
.impersonation-btn {
    background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.impersonation-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.impersonation-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

.rate-limit-warning {
    color: #dc3545;
    font-weight: bold;
}

.rate-limit-info {
    color: #6c757d;
    font-size: 0.875rem;
}
```

### 5. Laravel Controller (if using Laravel for admin panel)

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RestaurantImpersonationController extends Controller
{
    public function impersonate(Request $request)
    {
        $request->validate([
            'restaurant_uid' => 'required|string',
            'restaurant_name' => 'required|string'
        ]);
        
        try {
            // Make API call to restaurant panel
            $response = Http::post('https://restaurant.jippymart.in/admin/impersonate/generate-token', [
                'restaurant_uid' => $request->restaurant_uid,
                'admin_id' => auth()->id(),
                'admin_email' => auth()->user()->email
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['success']) {
                    // Log the impersonation
                    \Log::info('Admin impersonation initiated', [
                        'admin_id' => auth()->id(),
                        'restaurant_uid' => $request->restaurant_uid,
                        'restaurant_name' => $request->restaurant_name
                    ]);
                    
                    return response()->json([
                        'success' => true,
                        'redirect_url' => $data['data']['redirect_url']
                    ]);
                }
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate impersonation token'
            ], 500);
            
        } catch (\Exception $e) {
            \Log::error('Impersonation failed', [
                'error' => $e->getMessage(),
                'admin_id' => auth()->id(),
                'restaurant_uid' => $request->restaurant_uid
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Network error occurred'
            ], 500);
        }
    }
}
```

### 6. Admin Panel Routes

```php
// In your admin panel routes file
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::post('/restaurants/impersonate', [RestaurantImpersonationController::class, 'impersonate'])
         ->name('admin.restaurants.impersonate');
});
```

## Security Considerations

### 1. CORS Configuration
Make sure your restaurant panel allows requests from your admin panel:

```php
// In restaurant panel config/cors.php
'allowed_origins' => [
    'https://admin.jippymart.in', // Your admin panel URL
    'https://your-admin-domain.com'
],
```

### 2. Authentication
- Ensure only authenticated admins can impersonate
- Implement proper role-based access control
- Log all impersonation attempts

### 3. Rate Limiting
- Monitor rate limit usage
- Implement additional rate limiting on admin panel side
- Alert when approaching limits

## Testing the Integration

### 1. Test Impersonation Flow
```javascript
// Test function
function testImpersonation() {
    const testRestaurantUid = 'test_restaurant_uid';
    const testRestaurantName = 'Test Restaurant';
    
    console.log('Testing impersonation...');
    impersonateRestaurant(testRestaurantUid, testRestaurantName);
}
```

### 2. Test Error Handling
```javascript
// Test with invalid data
function testErrorHandling() {
    impersonateRestaurant('invalid_uid', 'Test Restaurant');
}
```

### 3. Test Rate Limiting
```javascript
// Test rate limiting by making multiple requests
function testRateLimiting() {
    for (let i = 0; i < 12; i++) {
        setTimeout(() => {
            impersonateRestaurant('test_uid_' + i, 'Test Restaurant ' + i);
        }, i * 100);
    }
}
```

## Monitoring and Analytics

### 1. Track Impersonation Usage
```javascript
// Add to your analytics
function trackImpersonation(restaurantUid, restaurantName, success) {
    // Google Analytics
    gtag('event', 'impersonation_attempt', {
        'restaurant_uid': restaurantUid,
        'restaurant_name': restaurantName,
        'success': success
    });
    
    // Custom analytics
    fetch('/api/analytics/impersonation', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            restaurant_uid: restaurantUid,
            restaurant_name: restaurantName,
            success: success,
            admin_id: getCurrentAdminId(),
            timestamp: new Date().toISOString()
        })
    });
}
```

### 2. Dashboard Widget
Create a dashboard widget to show impersonation statistics:

```html
<div class="card">
    <div class="card-header">
        <h5>Impersonation Statistics</h5>
    </div>
    <div class="card-body">
        <div id="impersonation-stats">
            <p>Loading statistics...</p>
        </div>
    </div>
</div>
```

## Troubleshooting

### Common Issues:

1. **CORS Errors:**
   - Check CORS configuration in restaurant panel
   - Verify admin panel URL is in allowed origins

2. **Authentication Errors:**
   - Ensure admin is properly authenticated
   - Check CSRF token is valid

3. **Rate Limiting:**
   - Check if admin has exceeded rate limit
   - Verify cache is working properly

4. **Network Errors:**
   - Check if restaurant panel is accessible
   - Verify SSL certificates are valid

### Debug Mode:
```javascript
// Enable debug logging
window.DEBUG_IMPERSONATION = true;

// Add debug logging to functions
function debugLog(message, data) {
    if (window.DEBUG_IMPERSONATION) {
        console.log('[IMPERSONATION DEBUG]', message, data);
    }
}
```

---

This integration guide provides everything needed to implement the admin-side of the impersonation feature. The restaurant panel is already configured to handle the impersonation requests and auto-login functionality.
