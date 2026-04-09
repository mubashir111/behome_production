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
# 1. Pull latest code — stash any server-only local changes first so git
#    pull never aborts (e.g. package.json touched by npm ci, etc.)
# ---------------------------------------------------------------------------
echo "→ Pulling latest code"
git stash --quiet || true
git pull origin main
git stash pop --quiet || true   # restore server-only tweaks (if any)

# ---------------------------------------------------------------------------
# 2. PHP / Laravel
# ---------------------------------------------------------------------------
echo "→ Installing PHP dependencies"
COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader --no-interaction

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
  echo "→ Checking frontend .env.production"
  if [ ! -f frontend/.env.production ]; then
    echo "✖ ERROR: frontend/.env.production is missing!"
    echo "  Create it on the server before deploying:"
    echo "  cat > /var/www/behome_production/frontend/.env.production << 'EOF'"
    echo "  BACKEND_URL=https://api.behom.ae"
    echo "  IMAGE_HOSTNAME=api.behom.ae"
    echo "  API_KEY=your-api-key"
    echo "  NEXT_PUBLIC_SITE_URL=https://behom.ae"
    echo "  NEXT_PUBLIC_GOOGLE_CLIENT_ID=your-google-client-id"
    echo "  EOF"
    exit 1
  fi

  echo "→ Installing frontend dependencies"
  cd frontend
  npm ci

  echo "→ Building Next.js"
  npm run build

  cd ..

  echo "→ Restarting Next.js"
  pm2 restart behome-frontend || pm2 start npm --name "behome-frontend" -- start -- -p 3000
  pm2 save --quiet
fi

# ---------------------------------------------------------------------------
# 4. Queue / worker restart
# ---------------------------------------------------------------------------
echo "→ Restarting queue workers"
php artisan queue:restart || true

# ---------------------------------------------------------------------------
echo "✔ Deployment complete."
