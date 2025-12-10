# Quick Fix: Livewire Upload 401 Error

## Problem
When uploading files in production, getting error:
```
401 (Unauthorized) - POST https://qpay.yourin.my.id/livewire/upload-file
```

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

# Clear all caches
php artisan cache:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache

# Fix permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chown -R www-data:www-data storage
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

### 1. **Authentication Middleware Added** ✅
- New `HandleLivewireUploads` middleware ensures user is logged in
- Prevents unauthorized upload attempts

### 2. **Livewire Configuration Updated** ✅
- Middleware: `auth` (requires login)
- Disk: `local` (use local storage)
- Directory: `livewire-tmp` (temp upload directory)

### 3. **AppServiceProvider Enhanced** ✅
- Livewire upload configuration set properly
- HTTPS URLs forced in production
- Max upload size: 10MB

### 4. **Session Handling Improved** ✅
- Sessions stored in database
- HTTPS-only cookies
- Proper CSRF token handling

## Test Upload

1. Log in to https://qpay.yourin.my.id
2. Navigate to any page with file upload
3. Try uploading a file
4. Should work without 401 errors

## Check Logs if Still Having Issues

```bash
# Check Nginx error log
tail -f /var/log/nginx/qpay-error.log

# Check Laravel error log
tail -f /var/log/qpay/laravel.log

# Check if upload directory exists
ls -la /var/www/qpay/storage/livewire-tmp/
```

## Production Deployment Checklist

- [ ] Pull latest code: `git pull origin master`
- [ ] Run all fix commands above
- [ ] Restart PHP and web server
- [ ] Test file upload
- [ ] Check error logs
- [ ] Verify permissions: `ls -la storage/`
- [ ] Verify storage link: `ls -la public/storage`

## More Details

For complete troubleshooting guide, see: [LIVEWIRE_UPLOAD_FIX.md](LIVEWIRE_UPLOAD_FIX.md)
