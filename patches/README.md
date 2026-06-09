# Patches Directory

This directory contains custom patches for third-party dependencies that have issues or require workarounds.

## Patches Applied

### symfony-http-foundation-request-parse-body.patch

**Issue**: `Call to undefined function Symfony\Component\HttpFoundation\request_parse_body()`

**When it occurs**: PATCH, PUT, or DELETE requests fail with 500 error when PECL HTTP extension is not available.

**Fix**: Modified `Request::createFromGlobals()` to safely check if `request_parse_body()` function exists before calling it. Falls back to `$_POST` and `$_FILES` superglobals if the function is unavailable.

**Applied to**: `vendor/symfony/http-foundation/Request.php` (line 285-297)

## Applying Patches

To apply these patches automatically during `composer install`, use the [cweagans/composer-patches](https://github.com/cweagans/composer-patches) package:

```bash
composer require --dev cweagans/composer-patches
```

Then add to `composer.json`:

```json
{
  "extra": {
    "patches": {
      "symfony/http-foundation": {
        "Fix request_parse_body fallback": "patches/symfony-http-foundation-request-parse-body.patch"
      }
    }
  }
}
```

## References

- **Date Applied**: 2026-06-09
- **Issue**: QPAY Production - Product update (generate AI image) endpoints returning 500
- **System**: PHP 8.2.31, Symfony 8.0.8, Laravel
