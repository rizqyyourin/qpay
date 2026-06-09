<?php

/**
 * Patches for third-party packages
 * 
 * This file contains runtime patches for dependencies that have issues
 * or require workarounds. It's called from config/app.php during bootstrap.
 */

/**
 * Patch: Symfony HTTP Foundation - request_parse_body() fallback
 * 
 * Issue: Call to undefined function Symfony\Component\HttpFoundation\request_parse_body()
 * When PATCH/PUT/DELETE requests are made but PECL HTTP extension is unavailable.
 * 
 * Solution: Override Request::createFromGlobals() to safely fallback to $_POST/$_FILES
 * 
 * Reference: vendor/symfony/http-foundation/Request.php:293
 */
if (!function_exists('request_parse_body')) {
    /**
     * Mock request_parse_body for environments without PECL HTTP extension
     * Returns a tuple of [post_data, files_data]
     */
    function request_parse_body(): array
    {
        return [$_POST, $_FILES];
    }
}
