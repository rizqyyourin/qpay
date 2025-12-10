# Quick Fix: Livewire Upload 401 Error

## Problem
When uploading files in production, getting error:
```
401 (Unauthorized) - POST https://qpay.yourin.my.id/livewire/upload-file
```

## Root Cause
Livewire upload route wasn't protected with authentication middleware, causing browser to reject requests without proper session/auth.

## Quick Fix (3 Steps)

### Step 1: Pull Latest Code
```bash
cd /var/www/qpay
git pull origin master
```

### Step 2: Run Commands
```bash
# Create storage link for public access
php artisan storage:link

# Clear all caches (IMPORTANT!)
php artisan cache:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache

# Fix permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chown -R www-data:www-data storage bootstrap
```

### Step 3: Restart Services
```bash
# Restart PHP-FPM
sudo systemctl restart php8.4-fpm

# Restart Nginx/Apache
sudo systemctl restart nginx
# OR
sudo systemctl restart apache2
```

## What Was Fixed

### 1. **Livewire Route Protection** ✅
- Added explicit route definition with `auth` middleware
- Route: `/livewire/upload-file` now requires authentication
- Livewire signed URLs now properly validated

### 2. **Configuration Updates** ✅
- Livewire middleware set to `throttle:60,1` for rate limiting
- File upload directory: `storage/livewire-tmp/`
- Max upload size: 10MB

### 3. **AppServiceProvider Enhanced** ✅
- Livewire configuration properly initialized
- HTTPS URLs forced in production
- Gate authorization for uploads

### 4. **Route Middleware** ✅
- Routes configured to require `auth` middleware
- Session cookies properly handled

## Test Upload

1. Log in to https://qpay.yourin.my.id
2. Navigate to any page with file upload
3. Try uploading a file
4. Should work without 401 errors ✅

## Verify Changes

```bash
# Check if storage link exists
ls -la /var/www/qpay/public/storage

# Should output:
# storage -> ../storage/app/public

# Check permissions
ls -la /var/www/qpay/storage/ | head -5

# Should show drwxrwx--- or similar with www-data owner
```

## Check Logs if Still Having Issues

```bash
# Check Nginx error log
tail -f /var/log/nginx/qpay-error.log

# Check Laravel error log
tail -f /var/log/qpay/laravel.log

# Check if upload directory exists and is writable
ls -la /var/www/qpay/storage/livewire-tmp/
```

## Production Deployment Checklist

- [ ] Pull latest code: `git pull origin master`
- [ ] Run cache clear commands (VERY IMPORTANT!)
- [ ] Create storage link: `php artisan storage:link`
- [ ] Fix permissions
- [ ] Restart PHP-FPM and web server
- [ ] Test file upload
- [ ] Check error logs for any issues
- [ ] Verify directory permissions: `ls -la storage/`
- [ ] Verify storage link: `ls -la public/storage`

## Important Notes

1. **Cache Clearing is Critical** - Old cached routes will still cause 401
2. **Storage Link Must Exist** - Without it, uploaded files won't be accessible
3. **Permissions Must Be Correct** - www-data user must be able to write to storage

## Troubleshooting

### Still getting 401?

1. **Clear caches again:**
   ```bash
   php artisan cache:clear
   php artisan route:cache
   ```

2. **Check browser cache:**
   - Open DevTools → Application → Storage → Clear Site Data
   - Refresh page (Ctrl+F5)

3. **Check session:**
   ```bash
   # Verify user session in database
   SELECT * FROM sessions WHERE user_id = 1 LIMIT 1;
   ```

### Upload works but file not found?

1. **Create storage link if missing:**
   ```bash
   php artisan storage:link
   ls -la public/storage
   ```

2. **Check filesystem config:**
   ```bash
   # Verify config/filesystems.php has proper disk setup
   cat config/filesystems.php | grep -A 10 "'public'"
   ```

## For More Details

See: [LIVEWIRE_UPLOAD_FIX.md](LIVEWIRE_UPLOAD_FIX.md)

