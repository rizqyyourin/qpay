# Livewire File Upload Fix for Production

## Problem
When deploying to production at `https://qpay.yourin.my.id`, file uploads fail with:
```
401 (Unauthorized) - POST https://qpay.yourin.my.id/livewire/upload-file
```

## Root Cause
Livewire upload endpoint requires authentication, but the default configuration doesn't properly handle:
1. Authentication middleware for file uploads
2. HTTPS/URL scheme configuration
3. Proper session handling for upload requests

## Solution Implemented

### 1. Updated Livewire Configuration (config/livewire.php)
- Set `'middleware' => 'auth'` to require authentication for uploads
- Set `'disk' => 'local'` explicitly
- Set `'directory' => 'livewire-tmp'` for temp file storage

### 2. Enhanced AppServiceProvider (app/Providers/AppServiceProvider.php)
- Added `Livewire::configureFileUploads()` configuration
- Set max upload size to 10MB
- Forced HTTPS scheme for all URLs in production

### 3. Created Middleware (app/Http/Middleware/HandleLivewireUploads.php)
- Handles authentication verification for upload routes
- Manages CORS preflight requests
- Provides proper error responses with 401 status

### 4. Registered Middleware (bootstrap/app.php)
- Added HandleLivewireUploads to web middleware stack

## Production Deployment Checklist

### Before Deploying to Production

#### 1. Storage Directory Permissions
```bash
# Create symbolic link for public storage access
php artisan storage:link

# Set correct permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chown -R www-data:www-data storage/
```

#### 2. Web Server Configuration (Nginx)
```nginx
# In your Nginx config, ensure:

# 1. Livewire upload route is protected by auth
location /livewire/upload-file {
    fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
    fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    include fastcgi_params;
}

# 2. Static files in storage are publicly accessible
location /storage {
    alias /var/www/qpay/storage/app/public;
    expires 1y;
    add_header Cache-Control "public, immutable";
}

# 3. HTTPS is enforced
listen 443 ssl http2;
ssl_protocols TLSv1.2 TLSv1.3;
```

#### 3. Livewire Environment Variables
```env
# .env production settings
APP_ENV=production
APP_DEBUG=false
APP_URL=https://qpay.yourin.my.id

# Session security for upload requests
SESSION_DRIVER=database
SESSION_SECURE_COOKIES=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=strict
SESSION_ENCRYPT=true

# File uploads
FILESYSTEM_DISK=local
```

### After Deploying

#### 1. Test File Uploads
```bash
# Create test file
php artisan tinker
> auth()->loginUsingId(1); // Login as user 1 for testing
> // Ctrl+D to exit

# Then test upload functionality in browser
# Go to any page with file upload
# Try uploading a file
```

#### 2. Check Upload Directory
```bash
# Verify temp uploads directory exists and has proper permissions
ls -la storage/livewire-tmp/

# Check file was uploaded
# Should be readable by web server (www-data)
```

#### 3. Monitor Logs
```bash
# Watch error logs for upload issues
tail -f /var/log/nginx/qpay-error.log
tail -f /var/log/qpay/laravel.log

# Check for 401 errors on /livewire/upload-file endpoint
```

### Troubleshooting

#### Still Getting 401 Errors

1. **Check Session Middleware**
   ```bash
   # Ensure session is being set correctly
   # Check browser cookies for XSRF-TOKEN and qpay_session
   ```

2. **Check Database Sessions**
   ```bash
   # Verify sessions table has proper data
   php artisan migrate --env=production
   
   # Check sessions table
   SELECT * FROM sessions WHERE user_id = 1;
   ```

3. **Check File Permissions**
   ```bash
   # Ensure www-data can write to storage
   ls -la /var/www/qpay/storage/
   # Should show drwxrwx--- or similar with www-data owner
   ```

4. **Check Nginx/Apache Config**
   ```bash
   # Verify upload route doesn't have additional restrictions
   # Check for additional auth layers (basic auth, IP restrictions, etc)
   ```

#### Upload Works but File Not Found

1. **Create Storage Link**
   ```bash
   php artisan storage:link
   
   # Verify link was created
   ls -la public/storage
   # Should point to storage/app/public
   ```

2. **Check Disk Configuration (config/filesystems.php)**
   ```php
   'local' => [
       'driver' => 'local',
       'root' => storage_path('app'),
       'url' => env('APP_URL').'/storage',
       'visibility' => 'private',
   ],
   
   'public' => [
       'driver' => 'local',
       'root' => storage_path('app/public'),
       'url' => env('APP_URL').'/storage',
       'visibility' => 'public',
   ],
   ```

#### Performance Issues with Large Uploads

1. **Increase PHP Limits**
   ```ini
   ; /etc/php/8.4/fpm/php.ini
   upload_max_filesize = 100M
   post_max_size = 100M
   max_execution_time = 300
   max_input_time = 300
   ```

2. **Increase Nginx Limits**
   ```nginx
   # In nginx.conf
   client_max_body_size 100M;
   ```

3. **Configure Session Timeout**
   ```env
   SESSION_LIFETIME=480  # 8 hours in minutes
   ```

### Livewire Upload Flow

1. User opens page with file input
2. User selects file to upload
3. Livewire sends POST request to `/livewire/upload-file` with:
   - File binary data
   - Expires timestamp
   - Signature token
   - Session cookie (XSRF-TOKEN)
4. HandleLivewireUploads middleware:
   - Verifies request is to `/livewire/upload-file*`
   - Checks if user is authenticated
   - Returns 401 if not authenticated
   - Allows request if authenticated
5. Livewire handles:
   - Validates file (max size, type, etc)
   - Stores in `storage/livewire-tmp/`
   - Returns temporary file ID to component
6. Component can:
   - Preview file
   - Store permanently when form submits
   - Delete temporary file if cancelled

### Key Files Modified

1. `config/livewire.php` - Upload configuration
2. `app/Providers/AppServiceProvider.php` - HTTPS and upload setup
3. `app/Http/Middleware/HandleLivewireUploads.php` - NEW - Authentication middleware
4. `bootstrap/app.php` - Middleware registration
5. `.env` - Environment variables (already configured)

### Testing in Local Development

```bash
# Start with APP_ENV=local to test without SSL requirements
php artisan serve

# Create test component with file upload
php artisan make:livewire TestFileUpload

# Test upload functionality
# Should work without 401 errors
```

### Security Considerations

1. **Authentication Required** - All uploads require authenticated user
2. **File Validation** - Only allowed MIME types can be uploaded
3. **Size Limits** - Maximum 10MB per file (configurable)
4. **Temporary Cleanup** - Files auto-deleted after 24 hours
5. **HTTPS Only** - Production uses secure HTTPS connections
6. **CSRF Protection** - All requests include XSRF token

### Reference Links

- [Livewire File Uploads Docs](https://livewire.laravel.com/docs/file-uploads)
- [Laravel File Storage](https://laravel.com/docs/11.x/filesystem)
- [Laravel Sessions](https://laravel.com/docs/11.x/session)
