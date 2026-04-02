# 🛡️ Behome - Security Hardening Guide

Follow these steps to secure your server and application before going live.

---

## 1. Secure Database Setup
**Never use the `root` user for your application.**

1.  **Create a dedicated user:**
    ```sql
    CREATE USER 'behome_prod'@'localhost' IDENTIFIED BY 'Strong_Password_Here';
    GRANT ALL PRIVILEGES ON behome_db.* TO 'behome_prod'@'localhost';
    FLUSH PRIVILEGES;
    ```
2.  **External Access:** Ensure your database is only accessible via `localhost` (127.0.0.1) unless you are using a managed service (like RDS), in which case use a Security Group to limit access.

---

## 2. Laravel Configuration
1.  **Debug Mode:** Ensure `APP_DEBUG=false` in your `.env`.
2.  **Environment:** Ensure `APP_ENV=production`.
3.  **App Key:** Always generate a fresh key:
    ```bash
    php artisan key:generate
    ```
4.  **Optimal Caching:** Run these commands after every deployment:
    ```bash
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    ```

---

## 3. Server-Level Security (Ubuntu/Nginx)
1.  **File Permissions:**
    ```bash
    # Set owner to the web user (e.g., www-data)
    sudo chown -R behome:www-data /var/www/behome
    
    # Secure storage and cache
    sudo chmod -R 775 /var/www/behome/storage
    sudo chmod -R 775 /var/www/behome/bootstrap/cache
    ```
2.  **SSL/TLS:** Use [Certbot](https://certbot.eff.org/) to enable HTTPS.
3.  **Firewall (UFW):**
    ```bash
    sudo ufw allow 80/tcp
    sudo ufw allow 443/tcp
    sudo ufw allow ssh
    sudo ufw enable
    ```

---

## 4. Frontend Security
1.  **Environment Variables:** Build your frontend with production-specific variables.
    ```bash
    # /var/www/behome/frontend/.env.production
    NEXT_PUBLIC_API_URL=https://yourdomain.com/api
    NEXT_PUBLIC_SITE_URL=https://yourdomain.com
    ```
2.  **Linting:** Run `npm run lint` to ensure no common security or performance issues.

---

## 5. Ongoing Monitoring
1.  **Logs:** Regularly check `/var/www/behome/storage/logs/laravel.log`.
2.  **Queue Workers:** Use Supervisor (as documented in `DEPLOYMENT.md`) to keep your queue workers running safely.
