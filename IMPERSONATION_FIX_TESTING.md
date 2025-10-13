# Impersonation Redirect Fix - Testing Guide

## 🔧 What Was Fixed

The issue was that your restaurant panel has existing Firebase authentication listeners that were interfering with the impersonation redirect. The existing auth flow was redirecting to `/home` instead of `/dashboard` after impersonation login.

### Root Cause:
- Multiple Firebase `onAuthStateChanged` listeners were running simultaneously
- The existing auth listener was overriding the impersonation redirect
- Race condition between different auth flows

### Solution Applied:
1. **Added localStorage flags** to coordinate between auth listeners
2. **Set up dedicated auth listener** for impersonation flow
3. **Added override script** in main layout to handle conflicts
4. **Increased redirect delay** to ensure auth completion
5. **Better error handling** and cleanup

## 🧪 Testing Steps

### 1. Clear Browser Data
```bash
# Clear localStorage and cookies
# Open browser dev tools (F12)
# Go to Application tab → Storage → Clear storage
# Or manually clear:
localStorage.clear();
```

### 2. Test the Impersonation Flow

#### Step 1: Generate Token (from Admin Panel)
```javascript
// Test API call
fetch('https://restaurant.jippymart.in/admin/impersonate/generate-token', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': 'your_csrf_token'
    },
    body: JSON.stringify({
        restaurant_uid: 'test_restaurant_uid',
        admin_id: 'admin_123',
        admin_email: 'admin@test.com'
    })
})
.then(response => response.json())
.then(data => {
    console.log('Token generated:', data);
    if (data.success) {
        // Open the redirect URL
        window.open(data.data.redirect_url, '_blank');
    }
});
```

#### Step 2: Monitor Browser Console
Open browser dev tools and watch for these log messages:

**Expected Success Flow:**
```
🔐 Auto-login with impersonation token detected
✅ Successfully logged in with impersonation token
🔍 Waiting for auth state change to complete redirect...
✅ User authenticated during impersonation, redirecting to dashboard
```

**Expected Error Flow:**
```
❌ Auto-login failed: [error message]
```

#### Step 3: Verify Redirect
- Should redirect to `/dashboard` (not `/home`)
- Should show impersonation banner
- Should have impersonation data in localStorage

### 3. Test Error Scenarios

#### Test 1: Invalid Token
```javascript
// Manually test with invalid token
const testUrl = 'https://restaurant.jippymart.in/login?impersonation_token=invalid_token&restaurant_uid=test_uid&auto_login=true';
window.open(testUrl, '_blank');
```

#### Test 2: Expired Token
```javascript
// Wait 6+ minutes after generating token, then test
// Should show "Token expired or invalid" error
```

#### Test 3: Rate Limiting
```javascript
// Make 11+ requests within 1 hour
// Should get "Rate limit exceeded" error
```

### 4. Verify localStorage Data

After successful impersonation, check localStorage:

```javascript
// Check impersonation data
console.log('Impersonation data:', localStorage.getItem('restaurant_impersonation'));

// Should show:
{
    "isImpersonated": true,
    "restaurantUid": "restaurant_uid",
    "impersonatedAt": "2024-12-19T...",
    "cacheKey": "impersonation_token_xxxxx",
    "tokenUsed": true
}
```

### 5. Test Dashboard Access

After impersonation:
1. ✅ Should be logged in as restaurant owner
2. ✅ Should see impersonation banner
3. ✅ Should have access to restaurant dashboard
4. ✅ Should see restaurant data (foods, orders, etc.)

## 🔍 Debugging

### Check Console Logs
Look for these specific messages:

**Good Signs:**
- `🔐 Auto-login with impersonation token detected`
- `✅ Successfully logged in with impersonation token`
- `🔍 Waiting for auth state change to complete redirect...`
- `✅ User authenticated during impersonation, redirecting to dashboard`

**Warning Signs:**
- `❌ Auto-login failed:`
- `❌ Firebase not loaded`
- `Token UID mismatch`
- `Token expired or invalid`

### Check Network Tab
1. Open Dev Tools → Network tab
2. Look for API calls to `/admin/impersonate/generate-token`
3. Check response status and data
4. Verify redirect URL is correct

### Check localStorage
```javascript
// Check all impersonation-related data
console.log('impersonation_in_progress:', localStorage.getItem('impersonation_in_progress'));
console.log('impersonation_target_url:', localStorage.getItem('impersonation_target_url'));
console.log('restaurant_impersonation:', localStorage.getItem('restaurant_impersonation'));
```

## 🚨 Common Issues & Solutions

### Issue 1: Still Redirecting to `/home`
**Solution:** Clear browser cache and localStorage, then test again

### Issue 2: Firebase Not Loaded Error
**Solution:** Check if Firebase SDK is properly loaded in login page

### Issue 3: Token Generation Fails
**Solution:** 
- Check Firebase service account credentials
- Verify API endpoint is accessible
- Check CORS configuration

### Issue 4: Rate Limiting Issues
**Solution:**
- Wait 1 hour for rate limit reset
- Or clear cache: `Cache::flush()` in Laravel

### Issue 5: Impersonation Banner Not Showing
**Solution:**
- Check if localStorage data is properly set
- Verify banner script is running
- Check for JavaScript errors

## 📊 Success Criteria

✅ **Test Passes When:**
- [ ] Token generates successfully
- [ ] Auto-login works without errors
- [ ] Redirects to `/dashboard` (not `/home`)
- [ ] Impersonation banner appears
- [ ] User is logged in as restaurant owner
- [ ] Dashboard shows restaurant data
- [ ] No JavaScript errors in console
- [ ] localStorage contains impersonation data

## 🔄 Rollback Plan

If the fix causes issues, you can rollback by:

1. **Revert login.blade.php changes:**
```bash
git checkout HEAD~1 -- resources/views/auth/login.blade.php
```

2. **Revert app.blade.php changes:**
```bash
git checkout HEAD~1 -- resources/views/layouts/app.blade.php
```

3. **Clear cache:**
```bash
php artisan config:clear
php artisan cache:clear
```

## 📞 Support

If you encounter issues:

1. **Check browser console** for error messages
2. **Verify Firebase configuration** in `.env`
3. **Test API endpoints** manually
4. **Check Laravel logs** in `storage/logs/laravel.log`
5. **Verify CORS settings** for admin panel requests

## 🎯 Next Steps After Testing

Once testing is successful:

1. **Deploy to production**
2. **Monitor logs** for any issues
3. **Set up alerts** for failed impersonations
4. **Document the process** for your team
5. **Train admins** on the new feature

---

**Testing Status:** Ready for testing
**Last Updated:** December 2024
**Version:** 1.1.0 (Fixed)
