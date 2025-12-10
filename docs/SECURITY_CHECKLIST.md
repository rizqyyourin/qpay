# QPAY Security Checklist

## Pre-Deployment Security

### 1. Environment Configuration
- [ ] `.env` file created dengan APP_ENV=production
- [ ] APP_DEBUG=false di .env
- [ ] APP_KEY generated dengan: `php artisan key:generate`
- [ ] APP_URL set ke: https://qpay.yourin.my.id
- [ ] `.env` file permissions set ke 600
- [ ] `.env` file tidak tracked di Git

### 2. SSL/TLS Certificate
- [ ] Let's Encrypt certificate obtained untuk qpay.yourin.my.id
- [ ] Certificate valid dan tidak expired
- [ ] Certificate renewal setup (certbot auto-renewal)
- [ ] SSL Labs test menunjukkan A+ rating
- [ ] HTTPS redirect dari HTTP implemented

### 3. Web Server Configuration
- [ ] Nginx atau Apache configured dengan SSL
- [ ] SSL protocols: TLSv1.2 dan TLSv1.3
- [ ] Strong cipher suites configured
- [ ] Gzip compression enabled
- [ ] Static files caching configured
- [ ] Sensitive files (.env, .git, config/) blocked
- [ ] PHP file execution disabled di public folder (kecuali index.php)

### 4. Security Headers
- [ ] Strict-Transport-Security (HSTS) enabled
- [ ] X-Content-Type-Options: nosniff
- [ ] X-Frame-Options: DENY (prevent clickjacking)
- [ ] X-XSS-Protection enabled
- [ ] Referrer-Policy configured
- [ ] Permissions-Policy configured
- [ ] Content-Security-Policy configured

### 5. Database Security
- [ ] Database user created dengan limited privileges
- [ ] Strong password untuk database user
- [ ] Database connection via unix socket (jika localhost)
- [ ] Remote database connections hanya dari application server
- [ ] Database backups automated dan tested

### 6. File Permissions
- [ ] Web root: 755
- [ ] Public folder: 755
- [ ] Storage folder: 775
- [ ] Bootstrap/cache folder: 775
- [ ] .env file: 600
- [ ] Files owned by www-data:www-data

### 7. Application Configuration
- [ ] SESSION_SECURE_COOKIES=true
- [ ] SESSION_HTTP_ONLY=true
- [ ] SESSION_SAME_SITE=strict
- [ ] SESSION_ENCRYPT=true
- [ ] CSRF protection enabled
- [ ] Rate limiting configured

### 8. Database Integrity
- [ ] Foreign key constraints enabled
- [ ] Database indexes created
- [ ] Data validation rules implemented
- [ ] SQL injection prevention via Eloquent ORM

### 9. Logging & Monitoring
- [ ] LOG_LEVEL=warning (not debug)
- [ ] Application logs stored di /var/log/qpay/
- [ ] Error logs monitored regularly
- [ ] Access logs reviewed untuk suspicious activity
- [ ] Logging retention policy implemented

### 10. Backup & Disaster Recovery
- [ ] Daily automated database backups
- [ ] Backups tested untuk restore success
- [ ] Backup retention policy: 30 days minimum
- [ ] Backups stored di secure location
- [ ] Disaster recovery plan documented

### 11. Access Control
- [ ] SSH key-based authentication (no password auth)
- [ ] Firewall rules configured
- [ ] Only required ports open (80, 443)
- [ ] Database port tidak publicly accessible
- [ ] Admin panel (jika ada) rate-limited

### 12. Third-Party Services
- [ ] Mail service configured (SMTP)
- [ ] API keys stored secara aman di .env
- [ ] Third-party API connections via HTTPS
- [ ] API rate limiting implemented
- [ ] API authentication tokens rotated regularly

### 13. Performance & DDoS Protection
- [ ] Fail2Ban configured untuk brute-force protection
- [ ] Rate limiting implemented
- [ ] ModSecurity (WAF) installed dan configured
- [ ] DDoS mitigation measures in place
- [ ] Load balancing configured (jika traffic high)

### 14. Code Quality & Vulnerability
- [ ] Code reviewed oleh minimal 1 person
- [ ] No hardcoded credentials di code
- [ ] Dependencies updated: `composer update`
- [ ] Security vulnerabilities checked: `composer audit`
- [ ] Unit tests passed: `php artisan test`

### 15. Post-Deployment
- [ ] Application accessible di https://qpay.yourin.my.id
- [ ] HTTPS redirect working (http://qpay.yourin.my.id → https://...)
- [ ] Form submissions working (CSRF token)
- [ ] Database migrations executed
- [ ] Static assets loading correctly
- [ ] Error pages tested (404, 500)
- [ ] Admin panel accessible
- [ ] User authentication working
- [ ] Email notifications working
- [ ] Cron jobs scheduled dan running

## Ongoing Maintenance

### Daily
- [ ] Monitor error logs: `tail -f /var/log/qpay/laravel.log`
- [ ] Check disk space: `df -h`
- [ ] Monitor CPU/Memory usage

### Weekly
- [ ] Review access logs untuk suspicious activity
- [ ] Check certificate expiry: `certbot certificates`
- [ ] Verify automated backups completed
- [ ] Monitor application performance

### Monthly
- [ ] Update system packages: `apt update && apt upgrade`
- [ ] Review and rotate API keys/tokens
- [ ] Database optimization: `OPTIMIZE TABLE ...`
- [ ] Security audit dan penetration testing
- [ ] Review and update firewall rules

### Quarterly
- [ ] Full backup restore test
- [ ] Security vulnerability scan
- [ ] SSL certificate renewal verification
- [ ] Performance optimization review
- [ ] Disaster recovery plan review

### Annually
- [ ] Full security audit
- [ ] Penetration testing
- [ ] Code review untuk security issues
- [ ] Update SSL certificate
- [ ] Review dan update security policies

## Monitoring & Alerting Tools

### Recommended Tools
1. **SSL Monitoring**: Let's Encrypt auto-renewal
2. **Uptime Monitoring**: Uptime Robot, Pingdom
3. **Error Tracking**: Sentry
4. **Performance Monitoring**: New Relic, DataDog
5. **Log Aggregation**: ELK Stack, Splunk
6. **Security Scanning**: OWASP ZAP, Nessus

### Alert Configuration
- [ ] SSL certificate expiry alerts (30 days before)
- [ ] Disk space alerts (85% threshold)
- [ ] Error rate alerts (5% above baseline)
- [ ] Application downtime alerts
- [ ] Unusual traffic patterns alerts
- [ ] Database connection pool exhaustion alerts

## Emergency Procedures

### Application Crash
1. Check error logs: `tail -f /var/log/qpay/laravel.log`
2. Verify database connectivity
3. Check disk space
4. Restart PHP-FPM: `systemctl restart php8.4-fpm`
5. Restart web server: `systemctl restart nginx`
6. Verify application: `curl https://qpay.yourin.my.id`

### Database Issues
1. Check MySQL status: `systemctl status mysql`
2. Check MySQL logs: `tail -f /var/log/mysql/error.log`
3. Verify disk space
4. Restore dari latest backup (jika perlu)

### Security Breach
1. Isolate affected systems
2. Review access logs
3. Check untuk unauthorized changes
4. Rotate all credentials
5. Run full security audit
6. Notify affected users (jika data breach)

### DDoS Attack
1. Activate DDoS protection (CloudFlare, AWS Shield)
2. Enable rate limiting
3. Block suspicious IPs via firewall
4. Consult dengan hosting provider
5. Monitor untuk resumption

## Documentation

- [ ] Deployment guide documented
- [ ] Server configuration documented
- [ ] Database schema documented
- [ ] API documentation complete
- [ ] Emergency procedures documented
- [ ] Disaster recovery plan documented
- [ ] All documentation kept up-to-date
