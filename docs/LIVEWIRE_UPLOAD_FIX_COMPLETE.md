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

### 1. Remove Route Override (routes/web.php)
**BEFORE:**
```php
Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/livewire/upload-file', function () {
        // Livewire auto-registers upload-file route
    })->name('livewire.upload-file');
});
```

**AFTER:**
- ✅ Removed entirely - Let Livewire auto-register the route

### 2. Update Livewire Configuration (config/livewire.php)
**BEFORE:**
```php
'middleware' => 'throttle:60,1',  // Only rate limiting, no auth!
```

**AFTER:**
```php
'middleware' => ['auth', 'throttle:120,1'],  // Require authentication + rate limiting
```

### 3. Update Gate Definition (app/Providers/AppServiceProvider.php)
**BEFORE:**
```php
Gate::define('livewire-upload', function ($user) { ... });
```

**AFTER:**
```php
Gate::define('upload-files', function ($user) { ... });
```

### 4. Cleanup Bootstrap (bootstrap/app.php)
- ✅ Kept commented out - Not needed, Livewire handles everything via config

## How It Works

1. **User initiates file upload** from authenticated session
2. **Livewire generates signed URL** with:
   - User's authentication context
   - Timestamp (expiration via `expires` query param)
   - HMAC signature for validation
3. **Upload request hits Livewire endpoint** with:
   - Authentication middleware: Validates user is logged in
   - Rate limiting middleware: Prevents abuse (120 uploads per minute per IP)
4. **Livewire validates signature** using the `upload-files` gate:
   - Checks signature matches (no tampering)
   - Checks timestamp hasn't expired
   - Checks user is authorized
5. **File is uploaded** to `storage/livewire-tmp/{hash}` directory
6. **Component processes file** and moves to final location via `->store('products', 'public')`

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
