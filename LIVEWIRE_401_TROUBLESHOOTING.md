# LIVEWIRE 401 ERROR - PRODUCTION TROUBLESHOOTING

## Status: Still Getting 401?

Jika masih error 401, ini adalah checklist yang HARUS dijalankan:

## 1️⃣ CRITICAL: Verify APP_KEY is identical

Ini adalah penyebab PALING UMUM 401 error!

```bash
# On local machine:
cat .env | grep APP_KEY
# Result: APP_KEY=base64:8WT5nfgmvN16bpEtXwqZO9JM1mnhiK7lgbmHdfG/xYQ=

# On production server:
ssh user@your-domain.com
cat .env | grep APP_KEY
# Result: Should be IDENTICAL!
```

**If different:** Copy production key to local or vice versa.

The signed URL signature is generated using APP_KEY. If keys differ:
- Local: Signs URL with key A → `signature=abc123`
- Production: Validates with key B → signature invalid → 401 error

## 2️⃣ Deploy Latest Code

```bash
# Production server
cd /path/to/qpay
git pull origin master  # Gets simplified Livewire fix
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## 3️⃣ Verify Directories Exist & Are Writable

```bash
# Check temp upload directory
ls -la storage/app/livewire-tmp
# Should exist and be writable (permissions: drwxr-xr-x or similar)

# If doesn't exist, create it
mkdir -p storage/app/livewire-tmp
chmod 755 storage/app/livewire-tmp

# Check public storage
ls -la storage/app/public
# Should exist and be writable

# Check symlink
ls -la public/storage
# Should be a symlink → storage/app/public
```

## 4️⃣ Verify Timezone Configuration

```bash
# Check local PHP timezone
php -r "echo date_default_timezone_get();"
# Result: Asia/Jakarta (or your timezone)

# Check production PHP timezone  
php -r "echo date_default_timezone_get();"
# Result: Should match or at least be consistent

# Check Laravel config
php artisan config:get app.timezone
```

If timezones differ significantly (more than a few minutes), signature expiration might fail.

## 5️⃣ Check APP_DEBUG Setting

```bash
# Production .env
grep APP_DEBUG .env
# Should be: APP_DEBUG=false (or true for testing)

# If APP_DEBUG=false and error occurs:
#  1. Temporarily set APP_DEBUG=true
#  2. Clear caches
#  3. Test upload
#  4. Check storage/logs/laravel.log for details
#  5. Set back to false
```

## 6️⃣ Monitor Logs While Testing

In one terminal, tail the logs:
```bash
tail -f storage/logs/laravel.log
```

In another terminal, test upload through web interface.

Look for errors containing:
- `signature` - signature mismatch
- `expired` - timestamp expired
- `unauthorized` - gate denied
- `CSRF` - token issue

## 7️⃣ Browser Developer Tools

When upload fails with 401:
1. Open DevTools (F12)
2. Go to Network tab
3. Look for `livewire/upload-file` request
4. Right-click → Edit and Resend → see response body
5. Or click on request → Response tab → check error message

## 8️⃣ Test Simple Upload First

Don't test with large file. Try:
1. Login to dashboard
2. Go to Products
3. Click "Add Product"
4. Select SMALL image (< 1MB)
5. Try upload
6. Check console for exact error

## 9️⃣ Restart Services

After any .env or code changes:

```bash
# If using PHP-FPM
sudo systemctl restart php8.4-fpm

# If using Apache
sudo systemctl restart apache2

# If using Nginx
sudo systemctl restart nginx

# For supervisor/queue workers (if applicable)
sudo supervisorctl restart all
```

## 🔟 Nuclear Option - Full Redeploy

If nothing works:

```bash
# Stop all services
sudo systemctl stop php8.4-fpm  # or apache2

# Delete compiled files
rm -rf bootstrap/cache/*
rm -rf storage/logs/*.log

# Fresh setup
composer install --no-dev
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan storage:link
mkdir -p storage/app/livewire-tmp
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/

# Start services
sudo systemctl start php8.4-fpm  # or apache2

# Test
php artisan tinker
```

## Debugging Endpoint

You can access `/debug/livewire-upload` while logged in:
```
https://qpay.yourin.my.id/debug/livewire-upload
```

This shows:
- APP_KEY (first 20 chars)
- APP_ENV
- FILESYSTEM_DISK
- Storage paths
- Directory writable status
- Gate defined status
- USER_ID
- IS_AUTHENTICATED

## If Still 401 After All This

Please provide:
1. Output of `/debug/livewire-upload`
2. Last 50 lines of `storage/logs/laravel.log`
3. Output of `php artisan config:get app.key`
4. Output of `ls -la storage/app/`
5. Browser DevTools → Network → livewire/upload-file request Response

With this information we can diagnose the exact issue.
