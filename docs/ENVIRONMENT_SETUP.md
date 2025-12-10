# Environment Configuration Guide

## Files

- **`.env`** - Local development environment (default, used for local testing)
- **`.env.local`** - Alternative local configuration (optional, same as `.env`)
- **`.env.example`** - Template for production deployment (copy to `.env` on production)

## Local Development (`.env`)

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://qpay.test

DB_CONNECTION=sqlite
FILESYSTEM_DISK=public
SESSION_SECURE_COOKIES=false
SESSION_SAME_SITE=lax
SESSION_DOMAIN=null
LOG_LEVEL=debug
```

**Key Points:**
- `APP_DEBUG=true` - Shows error details during development
- `FILESYSTEM_DISK=public` - Uploads stored in `storage/app/public`, accessible via `/storage`
- `SESSION_SECURE_COOKIES=false` - Works on HTTP (localhost)
- `SESSION_DOMAIN=null` - Auto-detects domain (qpay.test)
- Database uses SQLite (no MySQL needed)

## Production Deployment (`.env.example` → `.env`)

1. **Copy template to production server:**
   ```bash
   cp .env.example .env
   ```

2. **Update production values:**
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY=base64:your-generated-key-here
   APP_URL=https://yourdomain.com

   DB_CONNECTION=mysql
   DB_HOST=your.db.host
   DB_USERNAME=username
   DB_PASSWORD=password
   DB_DATABASE=qpay

   FILESYSTEM_DISK=public
   SESSION_SECURE_COOKIES=true
   SESSION_DOMAIN=.yourdomain.com
   SESSION_SAME_SITE=strict
   ```

3. **Generate app key:**
   ```bash
   php artisan key:generate
   ```

4. **Run migrations:**
   ```bash
   php artisan migrate --force
   ```

5. **Link storage:**
   ```bash
   php artisan storage:link
   ```

## File Upload Configuration

### Local
- **Disk:** `public` (accessible at `/storage`)
- **Directory:** `storage/app/public/products`
- **Web Path:** `/storage/products`
- **Max Size:** 5MB per file

### Production
- **Same as local** - change FILESYSTEM_DISK to other options if using S3/cloud
- **Note:** Always run `php artisan storage:link` after deployment

## Session & Security Settings

| Setting | Local | Production | Reason |
|---------|-------|-----------|--------|
| `SESSION_SECURE_COOKIES` | false | true | Local is HTTP, Production is HTTPS |
| `SESSION_SAME_SITE` | lax | strict | Stricter CSRF protection in production |
| `SESSION_DOMAIN` | null | .yourdomain.com | Cookie domain matching |
| `SESSION_ENCRYPT` | false | true | Better security in production |
| `APP_DEBUG` | true | false | Never show errors in production |

## Troubleshooting

### Image Upload Not Working?
1. Check `FILESYSTEM_DISK=public` in `.env`
2. Run `php artisan storage:link` (creates symlink)
3. Verify `storage/app/public` is writable: `chmod -R 775 storage`
4. Clear cache: `php artisan config:clear`

### Session Issues?
- Check `SESSION_SECURE_COOKIES` matches your protocol (HTTP/HTTPS)
- Check `SESSION_DOMAIN` matches your domain
- Clear sessions: `php artisan session:clear`

### Database Not Found?
- Local: Ensure `database/database.sqlite` exists
- Production: Check MySQL connection credentials in `.env`
