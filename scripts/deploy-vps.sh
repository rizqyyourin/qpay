#!/usr/bin/env bash

set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/qpay}"
APP_HOST="${APP_HOST:-qpay.yourin.my.id}"
BRANCH="${BRANCH:-main}"
PHP_FPM_SERVICE="${PHP_FPM_SERVICE:-php8.2-fpm}"
PHP_FPM_BIN="${PHP_FPM_BIN:-php-fpm8.2}"
LOG_FILE="${LOG_FILE:-/tmp/qpay-deploy.log}"

exec > >(tee -a "$LOG_FILE") 2>&1

health_check() {
  curl --fail --silent --show-error --resolve "${APP_HOST}:443:127.0.0.1" "https://${APP_HOST}/" >/dev/null
}

echo "[$(date -Iseconds)] Starting qpay deploy in '$APP_DIR'"

cd "$APP_DIR"

current_branch="$(git rev-parse --abbrev-ref HEAD)"
if [[ "$current_branch" != "$BRANCH" ]]; then
  echo "Refusing deploy: current branch is '$current_branch', expected '$BRANCH'."
  exit 1
fi

if [[ -n "$(git status --porcelain)" ]]; then
  echo "Restoring tracked files modified by previous deploy..."
  git checkout -- .
fi

if [[ -n "$(git status --porcelain)" ]]; then
  echo "Refusing deploy: working tree is dirty (untracked/staged changes)."
  git status --short
  exit 1
fi

echo "[$(date -Iseconds)] Fetching latest branch '$BRANCH'"
git fetch origin "$BRANCH"
echo "[$(date -Iseconds)] Pulling latest branch '$BRANCH'"
git pull --ff-only origin "$BRANCH"

echo "[$(date -Iseconds)] Installing PHP dependencies"
COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-progress --ignore-platform-reqs

echo "[$(date -Iseconds)] Installing frontend dependencies"
if [[ -f pnpm-lock.yaml ]] && command -v pnpm >/dev/null 2>&1; then
  pnpm install --frozen-lockfile
elif [[ -f package-lock.json ]]; then
  npm ci
else
  npm install
fi

echo "[$(date -Iseconds)] Building frontend assets"
if command -v pnpm >/dev/null 2>&1 && [[ -f pnpm-lock.yaml ]]; then
  pnpm run build
else
  npm run build
fi

echo "[$(date -Iseconds)] Running database migrations"
php artisan migrate --force

if [[ ! -L public/storage ]]; then
  echo "[$(date -Iseconds)] Creating storage symlink"
  php artisan storage:link
fi

echo "[$(date -Iseconds)] Refreshing Laravel caches"
php artisan optimize:clear
php artisan optimize
php artisan queue:restart || true

echo "[$(date -Iseconds)] Fixing writable directory ownership"
chown -R www-data:www-data storage bootstrap/cache

if systemctl list-unit-files | grep -q "^${PHP_FPM_SERVICE}"; then
  echo "[$(date -Iseconds)] Validating ${PHP_FPM_BIN} configuration"
  "$PHP_FPM_BIN" -t
  echo "[$(date -Iseconds)] Reloading ${PHP_FPM_SERVICE}"
  systemctl reload "$PHP_FPM_SERVICE"
  systemctl is-active --quiet "$PHP_FPM_SERVICE"
fi

echo "[$(date -Iseconds)] Running local health check"
for attempt in 1 2 3 4 5; do
  if health_check; then
    echo "[$(date -Iseconds)] Health check passed on attempt ${attempt}"
    echo "Deploy complete for '${APP_HOST}'."
    exit 0
  fi

  if [[ "$attempt" -lt 5 ]]; then
    echo "[$(date -Iseconds)] Health check failed on attempt ${attempt}, retrying..."
    sleep 2
  fi
done

echo "[$(date -Iseconds)] Health check failed after 5 attempts"
exit 1
