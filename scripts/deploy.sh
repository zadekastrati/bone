#!/usr/bin/env bash
# Run on the server after git pull (PHP 8.2+, Node 18+, Composer 2.2+).
set -euo pipefail
cd "$(dirname "$0")/.."

composer install --no-dev --optimize-autoloader --no-interaction
npm ci
npm run build
php artisan storage:link --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache 2>/dev/null || true

echo "Build and Laravel caches complete."
