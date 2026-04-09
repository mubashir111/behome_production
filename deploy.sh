#!/usr/bin/env bash
# =============================================================================
# Behome — Production Deployment Script
# Usage: bash /root/deploy.sh [--skip-frontend] [--skip-migrate]
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

cd /var/www/behome_production

# ---------------------------------------------------------------------------
# 1. Pull latest code
# ---------------------------------------------------------------------------
echo "→ Pulling latest code"
git stash --quiet || true
git pull origin main
git stash pop --quiet || true

# ---------------------------------------------------------------------------
# 2. PHP / Laravel
# ---------------------------------------------------------------------------
echo "→ Installing PHP dependencies"
COMPOSER_ALLOW_SUPERUSER=1 php8.1 /usr/local/bin/composer install --no-dev --optimize-autoloader --no-interaction

if [ "$SKIP_MIGRATE" = false ]; then
  echo "→ Running database migrations"
  php8.1 artisan migrate --force
fi

echo "→ Clearing and rebuilding caches"
php8.1 artisan config:clear
php8.1 artisan route:clear
php8.1 artisan view:clear
php8.1 artisan event:clear

php8.1 artisan config:cache
php8.1 artisan route:cache
php8.1 artisan view:cache
php8.1 artisan event:cache

echo "→ Optimising class autoloader"
php8.1 artisan optimize

echo "→ Linking storage"
php8.1 artisan storage:link --quiet || true

# ---------------------------------------------------------------------------
# 3. Next.js frontend
# ---------------------------------------------------------------------------
if [ "$SKIP_FRONTEND" = false ]; then
  echo "→ Checking frontend .env.production"
  if [ ! -f frontend/.env.production ]; then
    echo "✖ ERROR: frontend/.env.production is missing!"
    echo "  Create it on the server before deploying:"
    echo "  BACKEND_URL=http://127.0.0.1"
    echo "  IMAGE_HOSTNAME=behom.ae"
    echo "  API_KEY=your-api-key"
    echo "  NEXT_PUBLIC_API_KEY=your-api-key"
    echo "  NEXT_PUBLIC_SITE_URL=https://behom.ae"
    echo "  NEXT_PUBLIC_GOOGLE_CLIENT_ID=your-google-client-id"
    exit 1
  fi

  echo "→ Installing frontend dependencies"
  cd frontend
  npm ci

  echo "→ Building Next.js"
  npm run build

  cd ..

  echo "→ Restarting Next.js"
  pm2 restart behome-frontend || pm2 start /var/www/behome_production/frontend/ecosystem.config.js
  pm2 save --quiet
fi

# ---------------------------------------------------------------------------
# 4. Queue / worker restart
# ---------------------------------------------------------------------------
echo "→ Restarting queue workers"
php8.1 artisan queue:restart || true

# ---------------------------------------------------------------------------
echo "✔ Deployment complete."