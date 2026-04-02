# 🔑 Behome - Production Secrets Checklist

Before going live, ensure all the following keys are obtained from their respective provider dashboards and added to your production `.env` file.

---

## 💳 Payment Gateways

### Stripe (Payments)
- [ ] **STRIPE_KEY:** Your public API key (starts with `pk_live_...`).
- [ ] **STRIPE_SECRET:** Your secret API key (starts with `sk_live_...`).
- [ ] **STRIPE_WEBHOOK_SECRET:** Obtained after creating a webhook endpoint in the Stripe dashboard (`whsec_...`).
    - *Endpoint URL:* `https://yourdomain.com/api/webhooks/stripe`

### PayPal (Payments)
- [ ] **PAYPAL_MODE:** Set to `live`.
- [ ] **PAYPAL_CLIENT_ID:** From your PayPal developer dashboard (Live App).
- [ ] **PAYPAL_CLIENT_SECRET:** Secret key for the Live App.
- [ ] **PAYPAL_WEBHOOK_ID:** From the Webhooks section of your app.

---

## 🔐 Authentication & Social

### Google OAuth
- [ ] **GOOGLE_CLIENT_ID:** From Google Cloud Console.
- [ ] **GOOGLE_CLIENT_SECRET:** From Google Cloud Console.
- [ ] **GOOGLE_REDIRECT_URI:** `https://yourdomain.com/auth/google/callback`

---

## 📧 Communications & Notifications

### Mail Server (SMTP)
- [ ] **MAIL_HOST:** e.g., `smtp.mailgun.org`, `email-smtp.us-east-1.amazonaws.com`.
- [ ] **MAIL_PORT:** usually `587` or `465`.
- [ ] **MAIL_USERNAME:** e.g., your postmaster address.
- [ ] **MAIL_PASSWORD:** your SMTP password.

### Firebase (Push Notifications)
- [ ] **FCM_SECRET_KEY:** From Firebase Console (Project Settings -> Cloud Messaging).

---

## 🛠️ Infrastructure & API

### Internal Frontend-API Key
- [ ] **MIX_API_KEY:** A unique, random string (e.g., 32 characters).
    - *Action:* Must be **the same** in both root `.env` and `frontend/.env.production`.

### Pusher (Real-time updates)
- [ ] **PUSHER_APP_ID:** From Pusher dashboard.
- [ ] **PUSHER_APP_KEY:** Public app key.
- [ ] **PUSHER_APP_SECRET:** Application secret key.
- [ ] **PUSHER_APP_CLUSTER:** e.g., `mt1` or `eu`.

---

> [!IMPORTANT]
> **Key Safety:** Never share your secret keys (sk_..., secret, password) with anyone. If you suspect a key has been compromised, rotate it immediately in the provider dashboard.
