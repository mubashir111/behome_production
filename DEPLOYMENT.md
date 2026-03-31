# Behome — Production Deployment Guide

## Table of Contents
1. [Server Requirements](#1-server-requirements)
2. [Server Setup](#2-server-setup)
3. [Upload the Project](#3-upload-the-project)
4. [Run the Web Installer](#4-run-the-web-installer)
5. [Configure Nginx](#5-configure-nginx)
6. [SSL Certificate](#6-ssl-certificate)
7. [Build the Frontend](#7-build-the-frontend)
8. [Set Up the Queue Worker](#8-set-up-the-queue-worker)
9. [Set Up Stripe Webhooks](#9-set-up-stripe-webhooks)
10. [Final Checks](#10-final-checks)
11. [Ongoing Maintenance](#11-ongoing-maintenance)
12. [Troubleshooting](#12-troubleshooting)

---

## 1. Server Requirements

| Component | Minimum | Recommended |
|-----------|---------|-------------|
| OS | Ubuntu 22.04 LTS | Ubuntu 24.04 LTS |
| PHP | 8.1 | 8.2+ |
| MySQL | 8.0 | 8.0+ |
| Node.js | 18.x | 20.x LTS |
| RAM | 1 GB | 2 GB+ |
| Disk | 10 GB | 20 GB+ |

**Required PHP extensions:**
`openssl`, `pdo_mysql`, `mbstring`, `tokenizer`, `json`, `curl`, `xml`, `ctype`, `bcmath`, `zip`, `imagick`, `gd`

---

## 2. Server Setup

### Install dependencies (Ubuntu)

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2 + extensions
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-xml \
  php8.2-curl php8.2-zip php8.2-bcmath php8.2-gd php8.2-imagick \
  php8.2-tokenizer php8.2-json php8.2-ctype php8.2-intl

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install MySQL
sudo apt install -y mysql-server
sudo mysql_secure_installation

# Install Node.js 20 LTS
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Install Nginx
sudo apt install -y nginx

# Install Supervisor (for queue workers)
sudo apt install -y supervisor

# Install Certbot (for SSL)
sudo apt install -y certbot python3-certbot-nginx
```

### Create MySQL database

```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE behome CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'behome'@'localhost' IDENTIFIED BY 'your_strong_password';
GRANT ALL PRIVILEGES ON behome.* TO 'behome'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Create application user

```bash
sudo useradd -m -s /bin/bash behome
sudo mkdir -p /var/www/behome
sudo chown behome:www-data /var/www/behome
```

---

## 3. Upload the Project

### Option A — Git (recommended)

```bash
cd /var/www/behome
sudo -u behome git clone https://github.com/your-org/behome.git .
```

### Option B — FTP / SCP

Upload all project files to `/var/www/behome/` except:
- `node_modules/`
- `frontend/node_modules/`
- `frontend/.next/`
- `vendor/`
- `.env`

### Set file permissions

```bash
sudo chown -R behome:www-data /var/www/behome
sudo find /var/www/behome -type f -exec chmod 644 {} \;
sudo find /var/www/behome -type d -exec chmod 755 {} \;

# Laravel needs write access to these directories
sudo chmod -R 775 /var/www/behome/storage
sudo chmod -R 775 /var/www/behome/bootstrap/cache
```

### Install PHP dependencies

```bash
cd /var/www/behome
sudo -u behome composer install --no-dev --optimize-autoloader --no-interaction
```

### Create the .env file

```bash
sudo -u behome cp .env.example .env
sudo -u behome php artisan key:generate
```

> **Do not configure .env manually — the web installer will do this for you.**
> Only `APP_KEY` needs to be generated here.

---

## 4. Run the Web Installer

### Temporary Nginx config (before SSL)

Create `/etc/nginx/sites-available/behome-temp`:

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/behome/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/behome-temp /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

### Open the installer

Navigate to: **`http://yourdomain.com/install`**

Follow the steps:

| Step | What to enter |
|------|--------------|
| **Requirements** | Verify all checks are green. Fix any red items before continuing. |
| **Permissions** | Verify `storage/` and `bootstrap/cache/` show as writable. |
| **Terms** | Check the acceptance checkbox. |
| **Site Setup** | App Name, Backend URL (`https://yourdomain.com`), Frontend URL (`https://yourdomain.com`), SMTP credentials |
| **Database** | Host `127.0.0.1`, Port `3306`, DB name, username, password |
| **Final** | Click Finish — migrations run, caches built, storage linked automatically |

> **Default admin credentials after install:**
> - Email: `admin@example.com`
> - Password: `123456`
> **Change these immediately after first login.**

---

## 5. Configure Nginx

This project serves both the Laravel API (`/api/*`, `/admin/*`) and the Next.js frontend from a single domain.

### Option A — Single domain (recommended)

Next.js proxies all API calls via rewrites in `next.config.mjs`. Run both apps on the same server with Nginx as the reverse proxy.

Create `/etc/nginx/sites-available/behome`:

```nginx
# Redirect HTTP → HTTPS
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;

    ssl_certificate     /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    ssl_protocols       TLSv1.2 TLSv1.3;
    ssl_ciphers         HIGH:!aNULL:!MD5;

    client_max_body_size 50M;

    # Next.js frontend (port 3000)
    location / {
        proxy_pass         http://127.0.0.1:3000;
        proxy_http_version 1.1;
        proxy_set_header   Upgrade $http_upgrade;
        proxy_set_header   Connection 'upgrade';
        proxy_set_header   Host $host;
        proxy_cache_bypass $http_upgrade;
        proxy_set_header   X-Real-IP $remote_addr;
        proxy_set_header   X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header   X-Forwarded-Proto $scheme;
    }

    # Laravel backend — API, admin panel, storage
    location ~ ^/(api|admin|install|storage|sanctum)/ {
        root /var/www/behome/public;
        try_files $uri $uri/ /index.php?$query_string;

        location ~ \.php$ {
            fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
            include fastcgi_params;
        }
    }

    # Serve uploaded files directly
    location /storage {
        root /var/www/behome/public;
        try_files $uri =404;
    }
}
```

```bash
sudo rm /etc/nginx/sites-enabled/behome-temp   # remove temp config
sudo ln -s /etc/nginx/sites-available/behome /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

---

## 6. SSL Certificate

```bash
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

Certbot will automatically update your Nginx config and set up auto-renewal.

Verify auto-renewal works:
```bash
sudo certbot renew --dry-run
```

---

## 7. Build the Frontend

```bash
cd /var/www/behome/frontend

# Create the frontend production .env
cat > .env.production << 'EOF'
NEXT_PUBLIC_API_URL=https://yourdomain.com/api
NEXT_PUBLIC_API_KEY=your_api_key_from_env_file
NEXT_PUBLIC_SITE_URL=https://yourdomain.com
EOF

# Install dependencies and build
npm ci --omit=dev
npm run build
```

### Run Next.js with PM2

```bash
# Install PM2 globally
sudo npm install -g pm2

# Start Next.js
cd /var/www/behome/frontend
pm2 start npm --name "behome-frontend" -- start

# Save PM2 process list and enable on boot
pm2 save
pm2 startup   # follow the printed command
```

Useful PM2 commands:
```bash
pm2 status                          # check running processes
pm2 logs behome-frontend            # view logs
pm2 restart behome-frontend         # restart after a new build
pm2 reload behome-frontend          # zero-downtime reload
```

---

## 8. Set Up the Queue Worker

The queue worker processes emails (order notifications, OTP, subscriber mail) asynchronously.

### Create log directory

```bash
sudo mkdir -p /var/log/behome
sudo chown behome:behome /var/log/behome
```

### Copy and edit Supervisor config

```bash
sudo cp /var/www/behome/supervisor.conf /etc/supervisor/conf.d/behome.conf
```

Open the file and replace the placeholder paths:
```bash
sudo nano /etc/supervisor/conf.d/behome.conf
```

Change every occurrence of `/var/www/behome` to `/var/www/behome` (verify it matches your actual path) and `user=www-data` to `user=behome`.

### Start the workers

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
sudo supervisorctl status   # confirm both workers show RUNNING
```

---

## 9. Set Up Stripe Webhooks

1. Log in to your [Stripe Dashboard](https://dashboard.stripe.com)
2. Go to **Developers → Webhooks → Add endpoint**
3. Set the endpoint URL to: `https://yourdomain.com/api/webhooks/stripe`
4. Select event: **`charge.succeeded`**
5. Copy the **Signing secret** (starts with `whsec_...`)
6. Add it to your `.env`:

```bash
sudo -u behome nano /var/www/behome/.env
```

```env
STRIPE_WEBHOOK_SECRET=whsec_your_signing_secret_here
```

```bash
cd /var/www/behome
sudo -u behome php artisan config:cache
```

---

## 10. Final Checks

Run through this checklist before announcing the site is live:

```bash
# 1. Verify no debug mode
grep "APP_DEBUG\|APP_ENV" /var/www/behome/.env
# Should show: APP_ENV=production  APP_DEBUG=false

# 2. Verify caches are built
ls /var/www/behome/bootstrap/cache/
# Should show: config.php, routes-v7.php, events.php

# 3. Verify storage symlink exists
ls -la /var/www/behome/public/storage
# Should be a symlink → ../storage/app/public

# 4. Verify queue workers are running
sudo supervisorctl status

# 5. Verify Next.js is running
pm2 status

# 6. Test API is reachable
curl -s https://yourdomain.com/api/v1/products | head -c 100

# 7. Test sitemap is generated
curl -s https://yourdomain.com/sitemap.xml | head -c 200
```

### In the Admin Panel (`https://yourdomain.com/admin`)

- [ ] Change default admin password (`admin@example.com` / `123456`)
- [ ] Go to **Settings → Site** — upload logo, set timezone, currency
- [ ] Go to **Settings → Company** — fill in real company details
- [ ] Go to **Settings → Mail** — test mail by sending a test email
- [ ] Go to **Payment Gateways** — enable Stripe, enter your live API keys
- [ ] Go to **Shipping** — configure delivery zones and rates
- [ ] Go to **Settings → Notification** — configure order status email templates

---

## 11. Ongoing Maintenance

### Deploy new code changes

```bash
cd /var/www/behome
bash deploy.sh
# Flags: --skip-migrate (if no DB changes), --skip-frontend (if only backend changed)
```

The `deploy.sh` script automatically:
- Pulls latest code
- Runs `composer install`
- Runs migrations
- Rebuilds all Laravel caches
- Builds the Next.js frontend
- Runs `php artisan queue:restart` to reload workers

After `deploy.sh`, restart PM2:
```bash
pm2 restart behome-frontend
```

### View logs

```bash
# Laravel application logs
tail -f /var/www/behome/storage/logs/laravel.log

# Queue worker logs
tail -f /var/log/behome/worker-emails.log
tail -f /var/log/behome/worker-default.log

# Next.js logs
pm2 logs behome-frontend

# Nginx logs
tail -f /var/log/nginx/error.log
```

### Database backups

```bash
# Add to crontab (sudo crontab -e)
0 2 * * * mysqldump -u behome -pyour_password behome | gzip > /backups/behome-$(date +\%Y\%m\%d).sql.gz
```

---

## 12. Troubleshooting

### "500 Server Error" after deploy
```bash
cd /var/www/behome
php artisan optimize:clear   # clear all caches
php artisan migrate --force  # ensure DB is up to date
php artisan optimize         # rebuild caches
```

### Emails not sending
```bash
# Check workers are running
sudo supervisorctl status

# Check for failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Test mail config directly
php artisan tinker
>>> Mail::raw('Test', fn($m) => $m->to('you@email.com')->subject('Test'));
```

### Images not loading (404 on `/storage/...`)
```bash
php artisan storage:link --force
```

### Next.js shows old content after deploy
```bash
cd /var/www/behome/frontend
npm run build
pm2 restart behome-frontend
```

### Installer runs again after install (storage/installed missing)
```bash
# Recreate the installed flag file
echo "Behome installed on $(date)" > /var/www/behome/storage/installed
```

### Queue workers not starting after reboot
```bash
sudo supervisorctl reread && sudo supervisorctl update
sudo systemctl enable supervisor
```

---

> **NEXT_PUBLIC_API_KEY** — find this value in `/var/www/behome/.env` under `MIX_API_KEY` or the key you set during install. It must match between the Laravel `.env` and the frontend `.env.production`.
