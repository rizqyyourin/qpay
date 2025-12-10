# HTTPS Migration Guide - QPAY

## Problem Analysis

### Issues Found
1. **Mixed Content Errors** - Assets loading over HTTP when page is HTTPS
2. **Logout Form** - Form action was generating HTTP URLs instead of HTTPS
3. **Asset URLs** - CSS and JS files were loading from HTTP

### Root Cause
- `APP_URL` in `.env` was hardcoded or missing HTTPS
- AppServiceProvider wasn't forcing HTTPS for URL generation
- Assets needed rebuilding after HTTPS configuration

## Solution Implemented

### 1. Updated AppServiceProvider (app/Providers/AppServiceProvider.php)

**Before:**
```php
if ($this->app->environment('production')) {
    URL::forceScheme('https');
}
```

**After:**
```php
// Force HTTPS when APP_URL uses HTTPS
if (str_starts_with(config('app.url', ''), 'https://')) {
    URL::forceScheme('https');
}
```

**Why:** This ensures ALL URLs (routes, assets) are generated with HTTPS regardless of environment - development or production.

### 2. Updated .env Configuration

```env
APP_URL=https://qpay.yourin.my.id
```

**Why:** This tells Laravel that the application is served over HTTPS, so it generates all URLs with `https://` scheme.

### 3. Rebuilt Assets

```bash
npm run build
```

**Why:** Vite needs to rebuild manifest and asset references so they point to correct URLs.

### 4. Cleared Laravel Caches

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Why:** Caches need to be rebuilt with new AppServiceProvider configuration.

## URL Generation Helpers

### Laravel Helper Functions
All these helpers now generate HTTPS URLs:

```php
// Route helper - generates secure URLs
{{ route('logout') }}                    // https://qpay.yourin.my.id/logout
{{ route('profile.edit') }}              // https://qpay.yourin.my.id/profile
{{ route('dashboard') }}                 // https://qpay.yourin.my.id/dashboard

// Asset helper - generates secure URLs for static files
{{ asset('css/brand.css') }}             // https://qpay.yourin.my.id/css/brand.css
{{ asset('build/assets/app.css') }}      // https://qpay.yourin.my.id/build/assets/app-xxx.css

// URL helper - generates secure URLs
{{ url('/dashboard') }}                  // https://qpay.yourin.my.id/dashboard
{{ secure_url('/logout') }}              // https://qpay.yourin.my.id/logout
```

### Blade Directives
```blade
<!-- Routes -->
<a href="{{ route('home') }}">Home</a>

<!-- Assets -->
<link rel="stylesheet" href="{{ asset('css/brand.css') }}">
<script src="{{ asset('js/app.js') }}"></script>

<!-- Forms -->
<form action="{{ route('logout') }}" method="POST">
    @csrf
    <button type="submit">Logout</button>
</form>

<!-- Vite Assets -->
@vite(['resources/css/app.css', 'resources/js/app.js'])
```

## Verification

### Check URLs in Blade Templates
All templates use Laravel helpers, not hardcoded URLs:

✅ `{{ route() }}` - Generates HTTPS routes
✅ `{{ asset() }}` - Generates HTTPS assets
✅ `@vite()` - Generates HTTPS Vite assets
✅ `{{ url() }}` - Generates HTTPS URLs

### Browser Developer Tools
1. Open DevTools → Console
2. Check for "Mixed Content" errors
3. All CSS/JS should load from `https://`
4. All form actions should point to `https://`

### Test
1. Visit https://qpay.yourin.my.id
2. No mixed content warnings
3. Dashboard loads correctly
4. Logout form submits to HTTPS URL
5. All assets (CSS, JS) load successfully

## Files Modified

| File | Change | Purpose |
|------|--------|---------|
| `app/Providers/AppServiceProvider.php` | Updated `boot()` method | Force HTTPS URL generation based on APP_URL |
| `.env` | Verified `APP_URL=https://...` | Ensure HTTPS is set |
| `public/build/manifest.json` | Regenerated | Rebuilt assets with HTTPS refs |
| Config caches | Cleared | Force rebuild with new config |

## Best Practices for HTTPS

### 1. Always Use Helpers
❌ Don't do this:
```blade
<a href="http://qpay.yourin.my.id/profile">Profile</a>
<img src="http://qpay.yourin.my.id/logo.png">
```

✅ Do this:
```blade
<a href="{{ route('profile.edit') }}">Profile</a>
<img src="{{ asset('logo.png') }}">
```

### 2. Configuration
```env
# Production .env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://qpay.yourin.my.id

# Development .env
APP_ENV=local
APP_DEBUG=true
APP_URL=https://qpay.yourin.my.id  # Or http://localhost:8000
```

### 3. Web Server Configuration
Ensure web server redirects HTTP to HTTPS (see `docs/nginx.conf` or `docs/apache.conf`)

### 4. Security Headers
Add HSTS header (see `docs/nginx.conf`):
```
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
```

## Troubleshooting

### Still Getting Mixed Content Errors

**Solution 1: Clear All Caches**
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

**Solution 2: Rebuild Assets**
```bash
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Solution 3: Check APP_URL**
```bash
# Verify APP_URL is set to HTTPS
grep APP_URL .env
# Output should be: APP_URL=https://qpay.yourin.my.id
```

### Asset URLs Still HTTP

**Cause:** AppServiceProvider not properly forcing HTTPS

**Fix:**
1. Verify AppServiceProvider has the updated code
2. Clear config cache: `php artisan config:clear`
3. Rebuild assets: `npm run build`
4. Cache everything: `php artisan optimize`

### Logout Form Still HTTP

**Cause:** URL::forceScheme not working

**Fix:**
1. Check that `str_starts_with(config('app.url'), 'https://')` is true
2. Verify `.env` has `APP_URL=https://...`
3. Clear route cache: `php artisan route:clear`
4. Rebuild cache: `php artisan route:cache`

## Development vs Production

### Development (Local)
```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
# or
APP_URL=https://qpay.local  # If using local HTTPS
```

### Production
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://qpay.yourin.my.id
SESSION_SECURE_COOKIES=true
SESSION_HTTP_ONLY=true
```

## References

- [Laravel Helpers Documentation](https://laravel.com/docs/helpers#urls)
- [Laravel URL Generation](https://laravel.com/docs/urls)
- [OWASP Mixed Content](https://owasp.org/www-community/attacks/Manipulator-in-the-middle_attack)
- [HSTS Specification](https://tools.ietf.org/html/rfc6797)

## Testing Checklist

- [ ] No mixed content warnings in browser console
- [ ] All CSS files load from HTTPS
- [ ] All JavaScript files load from HTTPS
- [ ] Forms submit to HTTPS endpoints
- [ ] `route()` generates HTTPS URLs
- [ ] `asset()` generates HTTPS URLs
- [ ] External resources (CDN) use HTTPS
- [ ] SSL certificate is valid
- [ ] No certificate warnings in browser
