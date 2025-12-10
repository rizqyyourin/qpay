# Production Deployment Steps for Livewire Upload Fix

## On Your Production Server

```bash
# 1. Pull latest changes
cd /path/to/qpay
git pull origin master

# 2. Clear caches (CRITICAL!)
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# 3. Verify APP_KEY is set in .env
cat .env | grep APP_KEY

# 4. Test that authentication still works
php artisan tinker
>>> auth()->check()
false  # Should be false in tinker, expected
>>> exit()

# 5. Check storage is writable
ls -la storage/app/public/
# Should see 'products' directory and have write permissions

# 6. Verify symlink
ls -la public/storage
# Should be a symlink pointing to storage/app/public

# 7. Restart PHP-FPM (if using FPM) or restart service
sudo systemctl restart php8.4-fpm
# OR for Apache
sudo systemctl restart apache2

# 8. Monitor logs for any errors
tail -f storage/logs/laravel.log
```

## Quick Check List

- [ ] `git pull origin master` completed
- [ ] `php artisan config:clear && php artisan cache:clear` ran
- [ ] `.env` has APP_KEY set and identical to all other instances
- [ ] `.env` has `FILESYSTEM_DISK=public`
- [ ] `storage/app/livewire-tmp` directory exists and is writable
- [ ] `storage/app/public` directory writable (check with `ls -la`)
- [ ] `public/storage` symlink exists
- [ ] PHP-FPM/Apache restarted
- [ ] Test file upload works WITHOUT 401 error

## What Changed

```diff
# routes/web.php
+ Route::post('/livewire/upload-file', function () {
+     // Auto-handled by Livewire
+ })->middleware('throttle:120,1')->name('livewire.upload-file');

# config/livewire.php
- 'middleware' => ['auth', 'throttle:120,1'],
+ 'middleware' => 'throttle:120,1',

# app/Providers/AppServiceProvider.php
- Gate::define('livewire-upload', ...)
+ Gate::define('upload-files', function ($user) {
+     return $user !== null;  // User must be authenticated
+ })
```

## Critical: Check APP_KEY

This is the most common cause of 401 errors!

```bash
# On production server, get the APP_KEY
grep APP_KEY .env

# On local machine, compare
grep APP_KEY .env
```

**They MUST be identical!** The signed URL signature uses APP_KEY. If they differ:
- Local generates URL with key A
- Production validates with key B
- Signature doesn't match → 401 error

If they're different:
1. Copy APP_KEY from production: `cat .env | grep APP_KEY`
2. Set it on local (or use same for both)
3. Both environments must use same key for signature validation

## Testing Upload After Deployment

1. Log in to dashboard
2. Go to Products page
3. Click "Add Product" or edit existing
4. Try uploading an image
5. Should upload successfully without 401 error
6. Image should appear in product card

If 401 still occurs:
1. Check logs: `tail -f storage/logs/laravel.log`
2. Clear caches again: `php artisan config:clear && php artisan cache:clear`
3. Restart service again
4. Browser DevTools (F12) → Network → look for failed request details
