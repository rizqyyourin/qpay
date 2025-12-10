# LIVEWIRE 401 ERROR - PRODUCTION TROUBLESHOOTING

## Status: Getting 401 on File Uploads?

Jika upload gambar masih error 401, ikuti checklist ini step-by-step.

## ⚠️ MOST IMPORTANT: The REAL Root Cause

Livewire signed URLs memiliki expiry time embedded. If ada perbedaan **timezone** atau **server clock** antara local dan production:
- Local signs URL: `expires=1765330741` (dalam local time)
- Production validates: Checks against server time yang berbeda
- Jika perbedaan > 5 menit: URL dianggap expired → 401

## 1️⃣ Check Server Time & Timezone

```bash
# Production server - check current time
date
# Result: Should show current date/time

# Check timezone
timedatectl  # If available
# or
cat /etc/timezone
# or  
php -r "echo date_default_timezone_get();"
```

**Action if timezone wrong:**
```bash
# Set to correct timezone (Asia/Jakarta example)
sudo timedatectl set-timezone Asia/Jakarta

# Or update php.ini
sudo nano /etc/php/8.4/fpm/php.ini
# Find: date.timezone = 
# Set to: date.timezone = Asia/Jakarta
# Save and restart PHP

# Verify
php -r "echo date_default_timezone_get();"  # Should show Asia/Jakarta
```

## 2️⃣ Check APP_KEY Match

```bash
# Local machine
cat .env | grep APP_KEY
# Note down the value

# Production server
cat .env | grep APP_KEY
# MUST be identical!
```

If different - **this is 401 cause!**

## 3️⃣ Deploy Latest Code

```bash
# Production server
cd /path/to/qpay
git pull origin master
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

## 4️⃣ Verify Livewire Route Auto-Registers

```bash
# Production server
php artisan route:list | grep "livewire/upload"
# Should show: POST livewire/upload-file ... Livewire\Features ... FileUploadController@handle
```

If NOT showing, something's wrong with Livewire installation.

## 5️⃣ Check Directories

```bash
mkdir -p storage/app/livewire-tmp
mkdir -p storage/app/public
chmod -R 755 storage/app/livewire-tmp
chmod -R 755 storage/app/public
chmod -R 755 storage/framework/
chmod -R 755 bootstrap/cache/
```

## 6️⃣ Verify Symlink

```bash
ls -la public/storage
# Should show: storage -> ../../storage/app/public

# If missing:
php artisan storage:link
```

## 7️⃣ Check APP_DEBUG & Monitor Logs

```bash
# Temporarily enable debug
nano .env
# Set: APP_DEBUG=true
# Save

php artisan config:clear

# In another terminal, watch logs
tail -f storage/logs/laravel.log
```

Then test upload. Look for errors in logs related to:
- signature
- expired
- authorization
- CSRF
- gate

After testing, set `APP_DEBUG=false` again.

## 8️⃣ Restart Services

```bash
# For PHP-FPM
sudo systemctl restart php8.4-fpm

# For Apache
sudo systemctl restart apache2

# For Nginx
sudo systemctl restart nginx
```

## 9️⃣ Browser Network Tab Investigation

1. Open browser DevTools (F12)
2. Go to Network tab
3. Try uploading image
4. Find `livewire/upload-file` request (RED = failed)
5. Click on it → Response tab
6. Check error message

Common responses:
- `401 Unauthorized` - Signature failed or user not authenticated
- `419 Expired Token` - CSRF token issue, not upload
- `422 Unprocessable Entity` - File validation error
- `500 Internal Error` - Server error, check logs

## 🔟 The COMPLETE Fix (Most Reliable)

Run this on production server (one command block at a time):

```bash
# Step 1: Update code
cd /path/to/qpay
git pull origin master

# Step 2: Clear everything
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Step 3: Fix storage
rm -rf storage/app/livewire-tmp/*
mkdir -p storage/app/livewire-tmp
mkdir -p storage/app/public
chmod 755 storage/app/livewire-tmp
chmod 755 storage/app/public

# Step 4: Fix symlink
rm -f public/storage
php artisan storage:link

# Step 5: Restart service
sudo systemctl restart php8.4-fpm  # or apache2/nginx

# Step 6: Check timezone
php -r "echo 'Timezone: ' . date_default_timezone_get() . PHP_EOL . 'Time: ' . date('Y-m-d H:i:s') . PHP_EOL;"
```

## Debugging Endpoint (If you can access)

```
https://qpay.yourin.my.id/debug/livewire-upload
```

(Requires authentication)

Shows:
- APP_KEY (partial)
- APP_ENV
- FILESYSTEM_DISK
- Directories writable status
- Current time
- USER_ID
- IS_AUTHENTICATED

## Still 401? Then Provide This Info

1. Output of:
   ```bash
   php -r "echo 'Key: ' . env('APP_KEY') . PHP_EOL . 'TZ: ' . date_default_timezone_get() . PHP_EOL . 'Time: ' . date('Y-m-d H:i:s') . PHP_EOL;"
   ```

2. Last 20 lines of `storage/logs/laravel.log`

3. Browser DevTools Response for failed upload request

4. Output of:
   ```bash
   php artisan route:list | grep livewire
   ```

5. Output of:
   ```bash
   ls -la storage/app/
   ls -la public/storage
   ```

With this info, dapat identify exactly mana yang salah!
