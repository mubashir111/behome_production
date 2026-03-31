#!/usr/bin/env bash
# =============================================================================
# Behome — Production Deployment Script
# Usage: bash deploy.sh [--skip-frontend] [--skip-migrate]
# =============================================================================

set -euo pipefail

SKIP_FRONTEND=false
SKIP_MIGRATE=false

for arg in "$@"; do
  case $arg in
    --skip-frontend) SKIP_FRONTEND=true ;;
    --skip-migrate)  SKIP_MIGRATE=true  ;;
  esac
done

echo "▶ Behome deployment starting…"

# ---------------------------------------------------------------------------
# 1. Pull latest code
# ---------------------------------------------------------------------------
echo "→ Pulling latest code"
git pull origin main

# ---------------------------------------------------------------------------
# 2. PHP / Laravel
# ---------------------------------------------------------------------------
echo "→ Installing PHP dependencies"
composer install --no-dev --optimize-autoloader --no-interaction

if [ "$SKIP_MIGRATE" = false ]; then
  echo "→ Running database migrations"
  php artisan migrate --force
fi

echo "→ Clearing and rebuilding caches"
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "→ Optimising class autoloader"
php artisan optimize

echo "→ Linking storage"
php artisan storage:link --quiet || true

# ---------------------------------------------------------------------------
# 3. Next.js frontend
# ---------------------------------------------------------------------------
if [ "$SKIP_FRONTEND" = false ]; then
  echo "→ Installing frontend dependencies"
  cd frontend
  npm ci --omit=dev

  echo "→ Building Next.js"
  npm run build

  cd ..
fi

# ---------------------------------------------------------------------------
# 4. Queue / worker restart (adapt to your process manager)
# ---------------------------------------------------------------------------
echo "→ Restarting queue workers"
php artisan queue:restart || true

# ---------------------------------------------------------------------------
echo "✔ Deployment complete."
