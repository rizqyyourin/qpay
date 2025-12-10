# Production Deployment Guide - QPAY

## Domain Information
- **Domain**: qpay.yourin.my.id
- **Protocol**: HTTPS (SSL/TLS)
- **Environment**: Production

## Pre-Deployment Checklist

### 1. Environment Configuration
Pastikan file `.env` sudah dikonfigurasi dengan benar:

```env
# Application
APP_NAME=QPAY
APP_ENV=production
APP_DEBUG=false
APP_URL=https://qpay.yourin.my.id

# Key (generate dengan: php artisan key:generate)
APP_KEY=base64:YOUR_GENERATED_KEY_HERE

# Database (gunakan database production)
DB_CONNECTION=mysql
DB_HOST=your_db_host
DB_PORT=3306
DB_DATABASE=qpay_production
DB_USERNAME=qpay_user
DB_PASSWORD=strong_password_here

# Security - Session
SESSION_DRIVER=database
SESSION_LIFETIME=480
SESSION_SECURE_COOKIES=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=strict
SESSION_ENCRYPT=true

# Cache (gunakan Redis jika available)
CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Queue
QUEUE_CONNECTION=database

# Mail
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@qpay.yourin.my.id
MAIL_FROM_NAME=QPAY

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=warning
```

### 2. Laravel Configuration Changes

**config/app.php**:
```php
'timezone' => 'Asia/Kuala_Lumpur', // Sesuaikan dengan region
'locale' => 'en',
'fallback_locale' => 'en',
```

**config/session.php**:
```php
'secure' => true,           // HTTPS only
'http_only' => true,        // Disable JavaScript access
'same_site' => 'strict',    // CSRF protection
'encrypt' => true,          // Encrypt session data
```

### 3. SSL/TLS Certificate Setup

#### Option A: Let's Encrypt (Recommended - Free)
```bash
# Install Certbot
sudo apt-get update
sudo apt-get install certbot python3-certbot-nginx

# Generate certificate
sudo certbot certonly --nginx -d qpay.yourin.my.id

# Certificate location:
# /etc/letsencrypt/live/qpay.yourin.my.id/fullchain.pem
# /etc/letsencrypt/live/qpay.yourin.my.id/privkey.pem

# Auto-renewal setup
sudo certbot renew --dry-run
```

#### Option B: Paid SSL Certificate
1. Purchase SSL certificate dari provider (Sectigo, DigiCert, dll)
2. Install certificate di web server
3. Update web server config dengan paths

### 4. Web Server Configuration

#### Nginx Configuration
```nginx
# /etc/nginx/sites-available/qpay.yourin.my.id

# HTTP redirect ke HTTPS
server {
    listen 80;
    listen [::]:80;
    server_name qpay.yourin.my.id;
    
    return 301 https://$server_name$request_uri;
}

# HTTPS server
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name qpay.yourin.my.id;

    # SSL certificates
    ssl_certificate /etc/letsencrypt/live/qpay.yourin.my.id/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/qpay.yourin.my.id/privkey.pem;

    # SSL Security Headers
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;

    # Security Headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "DENY" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Permissions-Policy "geolocation=(), microphone=(), camera=()" always;

    # Root directory
    root /var/www/qpay/public;
    index index.php;

    # Logging
    access_log /var/log/nginx/qpay-access.log;
    error_log /var/log/nginx/qpay-error.log;

    # PHP-FPM configuration
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Static files cache
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Block sensitive files
    location ~ /\. {
        deny all;
    }

    location ~ ~$ {
        deny all;
    }

    # Rewrite rules untuk Laravel
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
}
```

#### Apache Configuration
```apache
# /etc/apache2/sites-available/qpay.yourin.my.id.conf

<VirtualHost *:80>
    ServerName qpay.yourin.my.id
    ServerAlias www.qpay.yourin.my.id
    
    # Redirect HTTP to HTTPS
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</VirtualHost>

<VirtualHost *:443>
    ServerName qpay.yourin.my.id
    ServerAlias www.qpay.yourin.my.id
    
    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/qpay.yourin.my.id/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/qpay.yourin.my.id/privkey.pem
    
    # SSL Security
    SSLProtocol -all +TLSv1.2 +TLSv1.3
    SSLCipherSuite HIGH:!aNULL:!MD5
    SSLHonorCipherOrder on
    
    # Security Headers
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "DENY"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    
    # Document Root
    DocumentRoot /var/www/qpay/public
    
    # Logging
    ErrorLog ${APACHE_LOG_DIR}/qpay-error.log
    CustomLog ${APACHE_LOG_DIR}/qpay-access.log combined
    
    # Laravel Rewrite Rules
    <Directory /var/www/qpay/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
        
        <IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteRule ^ index.php [QSA,L]
        </IfModule>
    </Directory>
</VirtualHost>
```

### 5. File Permissions & Ownership

```bash
# Set correct ownership
sudo chown -R www-data:www-data /var/www/qpay

# Set correct permissions
sudo chmod -R 755 /var/www/qpay
sudo chmod -R 755 /var/www/qpay/public
sudo chmod -R 775 /var/www/qpay/storage
sudo chmod -R 775 /var/www/qpay/bootstrap/cache

# Make .env readable only by owner
sudo chmod 600 /var/www/qpay/.env
```

### 6. Laravel Specific Security Configuration

**Add to AppServiceProvider.php**:
```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Force HTTPS in production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
```

### 7. Database Security

```sql
-- Create production database
CREATE DATABASE qpay_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create dedicated user
CREATE USER 'qpay_user'@'localhost' IDENTIFIED BY 'strong_password_here';

-- Grant privileges
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, INDEX, ALTER ON qpay_production.* TO 'qpay_user'@'localhost';

-- Flush privileges
FLUSH PRIVILEGES;

-- Run migrations
php artisan migrate --env=production
```

### 8. Application Deployment

```bash
# SSH ke server
ssh user@server

# Navigate ke application directory
cd /var/www/qpay

# Pull latest code
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader

# Generate application key (jika fresh installation)
php artisan key:generate

# Run migrations
php artisan migrate --force

# Optimize application
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Clear cache
php artisan cache:clear

# Set correct permissions
sudo chown -R www-data:www-data /var/www/qpay
sudo chmod -R 775 /var/www/qpay/storage
sudo chmod -R 775 /var/www/qpay/bootstrap/cache

# Restart services
sudo systemctl restart php8.4-fpm
sudo systemctl restart nginx  # atau apache2 jika pakai Apache
```

### 9. Monitoring & Maintenance

#### Check HTTPS Strength
```bash
# Test SSL configuration
openssl s_client -connect qpay.yourin.my.id:443

# Check certificate expiry
openssl x509 -in /etc/letsencrypt/live/qpay.yourin.my.id/fullchain.pem -noout -dates
```

#### Monitor Application
```bash
# Check log files
tail -f /var/log/nginx/qpay-error.log
tail -f /var/log/nginx/qpay-access.log

# Check disk space
df -h

# Check processes
ps aux | grep php
```

#### Certificate Auto-Renewal
```bash
# Setup cron job untuk auto-renewal (Let's Encrypt)
0 3 * * * /usr/bin/certbot renew --quiet --renew-hook "systemctl restart nginx"
```

### 10. DDoS & Security Tools

**Install ModSecurity (Nginx)**:
```bash
sudo apt-get install libnginx-mod-http-modsecurity
sudo systemctl reload nginx
```

**Configure Fail2Ban**:
```bash
sudo apt-get install fail2ban

# Create filter
sudo nano /etc/fail2ban/filter.d/nginx-laravel.conf

# Create jail
sudo nano /etc/fail2ban/jail.d/nginx-laravel.conf
```

### 11. Environment-Specific Settings

**Production .env**:
```env
APP_ENV=production
APP_DEBUG=false
DEBUGBAR_ENABLED=false

# Security
SESSION_SECURE_COOKIES=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=strict

# Performance
CACHE_STORE=redis
QUEUE_CONNECTION=database

# Logging
LOG_LEVEL=warning
```

### 12. SSL/TLS Testing

Test konfigurasi SSL menggunakan online tools:
- **SSL Labs**: https://www.ssllabs.com/ssltest/analyze.html?d=qpay.yourin.my.id
- **Mozilla Observatory**: https://observatory.mozilla.org/

Target untuk A+ rating.

## Security Checklist

- ✅ HTTPS/TLS 1.2+ enabled
- ✅ Strong cipher suites configured
- ✅ Security headers implemented
- ✅ HSTS enabled
- ✅ X-Frame-Options set to DENY
- ✅ X-Content-Type-Options set to nosniff
- ✅ X-XSS-Protection enabled
- ✅ CSRF protection active
- ✅ Database user dengan limited privileges
- ✅ File permissions yang benar (775 untuk storage)
- ✅ .env file tidak accessible (600)
- ✅ APP_DEBUG=false di production
- ✅ Sensitive files (.git, .env, etc) not publicly accessible
- ✅ SQL injection prevention via Eloquent ORM
- ✅ Session encryption enabled
- ✅ BCRYPT hashing untuk passwords
- ✅ Regular backups configured
- ✅ Error logging configured
- ✅ Certificate auto-renewal setup

## Post-Deployment

1. Verify aplikasi accessible di https://qpay.yourin.my.id
2. Test form submissions (CSRF token)
3. Test profile page (session handling)
4. Check SSL certificate validity
5. Monitor error logs
6. Setup monitoring tools (New Relic, Sentry, dll)
7. Setup automated backups
8. Document production setup

## Troubleshooting

### Mixed Content Warning
- Ensure all resources load over HTTPS
- Check .env APP_URL uses https://

### Certificate Errors
- Verify certificate is valid: `openssl x509 -in cert.pem -text -noout`
- Check certificate chain is complete
- Ensure DNS resolves correctly

### Performance Issues
- Enable query caching
- Use Redis for sessions/cache
- Optimize images and assets
- Enable gzip compression

### Database Connection Issues
- Check DB_HOST, DB_USER, DB_PASSWORD
- Verify firewall allows connection
- Check MySQL service is running
