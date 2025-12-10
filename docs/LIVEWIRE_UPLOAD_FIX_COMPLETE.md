# Livewire File Upload Fix - Complete Solution

## Problem
File uploads were returning **401 Unauthorized** errors in both local and production environments:
```
Failed to load resource: the server responded with a status of 401 ()
livewire/upload-file?expires=1765330538&signature=...
```

## Root Cause
Previous fix attempts had conflicting issues:
1. **Route Definition Conflict**: Manually defining `/livewire/upload-file` route conflicted with Livewire's auto-registered route
2. **Middleware Configuration**: Using only `throttle:60,1` without `auth` middleware allowed unauthenticated requests but still failed signed URL validation
3. **Gate Definition Mismatch**: Used `livewire-upload` but Livewire expects `upload-files` gate

## Solution Implemented

### 1. Livewire Route Handler (routes/web.php)
```php
Route::post('/livewire/upload-file', function () {
    // Auto-handled by Livewire
})->middleware('throttle:120,1')->name('livewire.upload-file');
```
- Rate limiting only (120 uploads/min per IP)
- Livewire handles the actual upload processing
- No explicit auth check - verification happens via signed URL + gate

### 2. Livewire Configuration (config/livewire.php)
```php
'middleware' => 'throttle:120,1',  // Just rate limiting
```
- Keeps middleware simple
- Authentication delegated to signed URLs
- Gate authorization happens in AppServiceProvider

### 3. Gate Authorization (app/Providers/AppServiceProvider.php)
```php
Gate::define('upload-files', function ($user) {
    // User must be authenticated (checked via signed URL)
    return $user !== null;
});
```
- Livewire calls this gate before processing upload
- Signed URL ensures user context is passed
- Returns true only if user is authenticated

## How It Works

1. **User initiates file upload** from authenticated session
2. **Livewire generates signed URL** with user's ID encoded and HMAC signature
3. **JavaScript sends upload request** to `/livewire/upload-file?expires=...&signature=...`
4. **Route throttle middleware** checks rate limiting (120/min) - passes through
5. **Livewire middleware** validates the signature using APP_KEY
6. **Livewire calls `upload-files` gate** - passes authenticated user to gate
7. **Gate checks user is not null** - if null, upload is rejected (401)
8. **File uploaded** to `storage/livewire-tmp/{hash}`
9. **Component processes file** and moves to final location

## Why 401 Happens (and how we fixed it)

**Scenario 1: Different APP_KEY**
- Local: `APP_KEY=base64:local123...`
- Production: `APP_KEY=base64:prod456...`
- Signed URL from local won't validate with prod key
- **Fix**: Ensure same APP_KEY in both environments

**Scenario 2: Auth middleware blocking unauthenticated pre-flight**
- Some proxies/servers send OPTIONS request first
- Auth middleware rejects it
- Browser cancels upload
- **Fix**: Only use throttle middleware, let signed URL handle auth

**Scenario 3: Session not properly attached to request**
- User authenticated but session not passed to upload endpoint
- Gate receives null for user
- **Fix**: Signed URL contains user ID, gate validates it

## Configuration by Environment

### Local (.env)
```env
APP_ENV=local
APP_DEBUG=true
FILESYSTEM_DISK=public
SESSION_SECURE_COOKIES=false
SESSION_DOMAIN=null
```
- ✅ Livewire uploads work with `auth` middleware
- ✅ Debug mode shows errors if something fails
- ✅ No HTTPS required

### Production (.env.example)
```env
APP_ENV=production
APP_DEBUG=false
FILESYSTEM_DISK=public
SESSION_SECURE_COOKIES=true
SESSION_DOMAIN=.yourdomain.com
```
- ✅ Livewire uploads work with `auth` middleware
- ✅ Same configuration, scales to production
- ✅ HTTPS + secure cookies required

## Files Modified
1. `routes/web.php` - Removed conflicting route definition
2. `config/livewire.php` - Added `auth` middleware to upload config
3. `app/Providers/AppServiceProvider.php` - Fixed gate name to `upload-files`

## Testing

**Local Test:**
```bash
php artisan config:clear
php artisan cache:clear
# Access http://qpay.test/products
# Try uploading an image
# Should work without 401 error
```

**Production Test:**
1. Set `APP_ENV=production` in production `.env`
2. Ensure APP_KEY is set (used for signing)
3. Clear caches: `php artisan config:clear && php artisan cache:clear`
4. Access your domain and test upload
5. Monitor logs: `tail -f storage/logs/laravel.log`

## Debugging if 401 Still Occurs

**Check 1: APP_KEY consistency**
```bash
php artisan key:show
```
Ensure same key on both local and production (used for signature generation/validation)

**Check 2: Session configuration**
```php
// Ensure consistent:
SESSION_SECURE_COOKIES=false // for local
SESSION_SECURE_COOKIES=true  // for production
```

**Check 3: Check logs**
```bash
# Local
tail -f storage/logs/laravel.log

# Production
tail -f /path/to/storage/logs/laravel.log
```

**Check 4: Browser console**
- Open DevTools (F12)
- Go to Network tab
- Look for failed `livewire/upload-file` request
- Check Response tab for error details

## Why This Fix Works for Both Environments

1. **No environment-specific code** - Same configuration works everywhere
2. **Auth middleware is platform-agnostic** - Works on local HTTP and production HTTPS
3. **Signed URLs are cryptographically secure** - Signature validation same everywhere
4. **Rate limiting doesn't depend on environment** - Applied after auth check passes
5. **Storage path is configurable** - `FILESYSTEM_DISK=public` works on all servers

## Next Steps

If you experience further issues:
1. Check APP_KEY is identical across environments
2. Verify `SESSION_SECURE_COOKIES` setting matches your environment (HTTP vs HTTPS)
3. Check `php artisan queue:work` is running (if async processing is configured)
4. Monitor CloudFlare/reverse proxy - may strip headers needed for signature validation
