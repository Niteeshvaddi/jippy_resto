# Admin Impersonation Feature - Complete Implementation Guide

## Overview

This implementation adds the "Admin Impersonates Restaurant" feature to your JippyMart restaurant panel. It allows admins to seamlessly log in as restaurant owners for support purposes using Firebase Custom Tokens.

## 🏗️ Architecture

```
Admin Panel → Restaurant Panel
     ↓              ↓
Generate Token → Auto-Login
     ↓              ↓
Rate Limiting → Dashboard Access
     ↓              ↓
Audit Logging → Impersonation Banner
```

## 📁 Files Modified/Created

### New Files Created:
1. `app/Http/Controllers/AdminImpersonationController.php` - Main controller
2. `config/firebase.php` - Firebase configuration
3. `ADMIN_IMPERSONATION_IMPLEMENTATION.md` - This documentation

### Files Modified:
1. `routes/web.php` - Added admin impersonation routes
2. `resources/views/auth/login.blade.php` - Added auto-login script
3. `resources/views/layouts/app.blade.php` - Added impersonation banner

## 🔧 Installation Steps

### 1. Install Firebase Admin SDK

```bash
composer require kreait/firebase-php
```

### 2. Environment Variables

Add these to your `.env` file:

```env
# Firebase Admin SDK Configuration
FIREBASE_PROJECT_ID=jippymart-27c08
FIREBASE_PRIVATE_KEY_ID=your_private_key_id
FIREBASE_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\nYour_Private_Key_Here\n-----END PRIVATE KEY-----\n"
FIREBASE_CLIENT_EMAIL=firebase-adminsdk-xxxxx@jippymart-27c08.iam.gserviceaccount.com
FIREBASE_CLIENT_ID=your_client_id
FIREBASE_CLIENT_X509_CERT_URL=https://www.googleapis.com/robot/v1/metadata/x509/firebase-adminsdk-xxxxx%40jippymart-27c08.iam.gserviceaccount.com

# Restaurant Panel URL
RESTAURANT_PANEL_URL=https://restaurant.jippymart.in
```

### 3. Firebase Service Account Setup

1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Select your project (`jippymart-27c08`)
3. Go to Project Settings → Service Accounts
4. Click "Generate new private key"
5. Download the JSON file and extract the values for your `.env`

### 4. Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

## 🚀 API Endpoints

### Generate Impersonation Token
```http
POST /admin/impersonate/generate-token
Content-Type: application/json

{
    "restaurant_uid": "restaurant_firebase_uid",
    "admin_id": "admin_user_id",
    "admin_email": "admin@example.com"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Impersonation token generated successfully",
    "data": {
        "impersonation_token": "eyJhbGciOiJSUzI1NiIs...",
        "restaurant_uid": "restaurant_firebase_uid",
        "cache_key": "impersonation_token_xxxxx",
        "expires_in": 300,
        "redirect_url": "https://restaurant.jippymart.in/login?impersonation_token=...&auto_login=true"
    }
}
```

### Validate Impersonation Token
```http
POST /admin/impersonate/validate-token
Content-Type: application/json

{
    "impersonation_token": "eyJhbGciOiJSUzI1NiIs...",
    "restaurant_uid": "restaurant_firebase_uid",
    "cache_key": "impersonation_token_xxxxx"
}
```

### Get Impersonation Statistics
```http
GET /admin/impersonate/stats?admin_id=admin_user_id
```

## 🔐 Security Features

### 1. Rate Limiting
- **Limit:** 10 impersonation attempts per hour per admin
- **Storage:** Laravel Cache (Redis/Database)
- **Reset:** Automatic after 1 hour

### 2. Token Security
- **Expiration:** 5 minutes
- **Single Use:** Tokens can only be used once
- **UID Validation:** Token UID must match restaurant UID

### 3. Audit Logging
- All impersonation attempts are logged
- Includes admin ID, restaurant UID, IP, and timestamp
- Failed attempts are also logged

### 4. Cache Management
- Tokens stored in cache with expiration
- Automatic cleanup after 5 minutes
- Prevents token reuse

## 🎯 Usage Flow

### For Admin Panel Integration:

```javascript
// 1. Admin clicks impersonation button
function impersonateRestaurant(restaurantUid) {
    const adminData = {
        restaurant_uid: restaurantUid,
        admin_id: getCurrentAdminId(),
        admin_email: getCurrentAdminEmail()
    };
    
    // 2. Generate impersonation token
    fetch('/admin/impersonate/generate-token', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken()
        },
        body: JSON.stringify(adminData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // 3. Open restaurant panel in new tab
            window.open(data.data.redirect_url, '_blank');
        } else {
            alert('Failed to generate impersonation token: ' + data.message);
        }
    });
}
```

### Restaurant Panel Auto-Login:

The restaurant panel automatically:
1. Detects impersonation parameters in URL
2. Shows loading indicator
3. Signs in with Firebase Custom Token
4. Validates token and UID
5. Stores impersonation info in localStorage
6. Redirects to dashboard
7. Shows impersonation banner

## 🎨 UI Components

### 1. Loading Indicator
- Full-screen overlay with spinner
- Professional loading message
- Auto-removes after login

### 2. Error Messages
- Toast notifications for errors
- Dismissible error alerts
- Clear error descriptions

### 3. Impersonation Banner
- Yellow warning banner on dashboard
- Shows impersonation timestamp
- Dismissible with close button
- Only shows when impersonated

## 📊 Monitoring & Analytics

### Log Events:
- `impersonation_token_generated`
- `impersonation_token_validated`
- `impersonation_token_expired`
- `impersonation_rate_limit_exceeded`

### Metrics to Track:
- Impersonation success rate
- Average session duration
- Most impersonated restaurants
- Admin usage patterns

## 🧪 Testing

### Test Cases:

1. **Successful Impersonation:**
   ```bash
   curl -X POST http://restaurant.jippymart.in/admin/impersonate/generate-token \
     -H "Content-Type: application/json" \
     -d '{"restaurant_uid":"test_uid","admin_id":"admin_123","admin_email":"admin@test.com"}'
   ```

2. **Rate Limiting:**
   - Make 11 requests within 1 hour
   - Should get 429 status on 11th request

3. **Token Expiration:**
   - Generate token, wait 6 minutes
   - Try to use token, should fail

4. **Invalid Token:**
   - Use malformed token
   - Should get validation error

### Manual Testing:
1. Generate token via API
2. Open redirect URL in browser
3. Verify auto-login works
4. Check impersonation banner appears
5. Verify dashboard access

## 🚨 Error Handling

### Common Errors:

1. **Rate Limit Exceeded:**
   ```json
   {
     "success": false,
     "message": "Rate limit exceeded. Maximum 10 impersonation attempts per hour."
   }
   ```

2. **Token Expired:**
   ```json
   {
     "success": false,
     "message": "Token expired or invalid"
   }
   ```

3. **Firebase Not Loaded:**
   - Shows error message in restaurant panel
   - Suggests page refresh

4. **UID Mismatch:**
   ```json
   {
     "success": false,
     "message": "Token UID mismatch"
   }
   ```

## 🔧 Configuration Options

### Environment Variables:
```env
# Firebase Configuration
FIREBASE_PROJECT_ID=jippymart-27c08
FIREBASE_PRIVATE_KEY_ID=your_key_id
FIREBASE_PRIVATE_KEY="your_private_key"
FIREBASE_CLIENT_EMAIL=your_service_account_email
FIREBASE_CLIENT_ID=your_client_id
FIREBASE_CLIENT_X509_CERT_URL=your_cert_url

# Panel URLs
RESTAURANT_PANEL_URL=https://restaurant.jippymart.in

# Rate Limiting (optional)
IMPERSONATION_RATE_LIMIT=10
IMPERSONATION_RATE_WINDOW=3600

# Token Expiration (optional)
IMPERSONATION_TOKEN_EXPIRY=300
```

### Customization Options:

1. **Rate Limiting:**
   - Modify `AdminImpersonationController.php`
   - Change limit from 10 to desired number

2. **Token Expiration:**
   - Change from 300 seconds (5 minutes) to desired duration

3. **Banner Styling:**
   - Modify CSS in `layouts/app.blade.php`
   - Customize colors, position, content

4. **Loading Indicator:**
   - Modify HTML/CSS in `auth/login.blade.php`
   - Add custom branding or animations

## 📈 Performance Considerations

### Optimizations:
1. **Cache Usage:** Tokens stored in fast cache (Redis recommended)
2. **Token Size:** Minimal custom claims to reduce token size
3. **Rate Limiting:** Efficient cache-based rate limiting
4. **Logging:** Async logging to prevent blocking

### Monitoring:
- Monitor cache hit rates
- Track API response times
- Monitor Firebase quota usage
- Watch for rate limit hits

## 🔄 Maintenance

### Regular Tasks:
1. **Log Rotation:** Clean up old impersonation logs
2. **Cache Cleanup:** Monitor cache usage
3. **Rate Limit Reset:** Check rate limit effectiveness
4. **Token Cleanup:** Ensure expired tokens are removed

### Updates:
1. **Firebase SDK:** Keep Firebase Admin SDK updated
2. **Security Patches:** Apply Laravel security updates
3. **Rate Limits:** Adjust based on usage patterns
4. **Monitoring:** Add more detailed analytics

## 🆘 Troubleshooting

### Common Issues:

1. **Firebase Not Initialized:**
   - Check Firebase configuration in `.env`
   - Verify service account permissions
   - Check Firebase project settings

2. **Token Generation Fails:**
   - Verify Firebase Admin SDK installation
   - Check service account JSON format
   - Ensure proper permissions

3. **Auto-Login Not Working:**
   - Check browser console for errors
   - Verify Firebase SDK is loaded
   - Check URL parameters

4. **Rate Limiting Issues:**
   - Check cache configuration
   - Verify cache driver is working
   - Check for cache key conflicts

### Debug Mode:
Enable debug logging by adding to `.env`:
```env
LOG_LEVEL=debug
```

## 📞 Support

For issues or questions:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check browser console for JavaScript errors
3. Verify Firebase configuration
4. Test API endpoints manually
5. Check cache and database connections

## 🎉 Success Criteria

✅ **Feature Complete When:**
- [ ] Admin can generate impersonation tokens
- [ ] Restaurant panel auto-logs in with token
- [ ] Rate limiting prevents abuse
- [ ] Impersonation banner shows on dashboard
- [ ] All security validations work
- [ ] Error handling covers edge cases
- [ ] Logging captures all events
- [ ] Performance is acceptable

## 🔮 Future Enhancements

### Potential Improvements:
1. **Session Management:** Track impersonation sessions
2. **Advanced Analytics:** Detailed usage reports
3. **Bulk Operations:** Impersonate multiple restaurants
4. **Time-based Access:** Schedule impersonation windows
5. **Approval Workflow:** Require approval for impersonation
6. **Mobile Support:** Optimize for mobile devices
7. **API Versioning:** Support multiple API versions
8. **Webhook Integration:** Notify external systems

---

**Implementation Status:** ✅ Complete
**Last Updated:** December 2024
**Version:** 1.0.0
