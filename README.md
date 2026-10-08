# PACH WOMEN — pachwomen.com

Online shop for PACH WOMEN. One Laravel app on one domain:

| URL | What |
|---|---|
| `/` | Shop (Blade + Livewire + Alpine.js + Tailwind CSS, built with Vite) |
| `/admin` | Admin panel (Filament): orders, products, categories, home banners, customers |

Stack: Laravel 13, Livewire 4, Filament 5, Tailwind CSS 4, Vite. SQLite locally, MySQL in production.

## Run locally

Needs [Laravel Herd](https://herd.laravel.com/windows) (PHP + Composer) and Node.js.

```bash
composer install
npm install
npm run build
php artisan migrate:fresh --seed
php artisan serve
```

Open http://127.0.0.1:8000. The admin login is `ADMIN_EMAIL` / `ADMIN_PASSWORD` in `.env`.
While editing views or CSS, run `npm run dev` in a second terminal for live reload.

Run the tests with `php artisan test`.

## Managing the shop (Admin)

Almost everything is edited at `/admin`, no code needed:

| Admin section | What you change there |
|---|---|
| Orders | Status, tracking number, payment status, delivery address |
| Products / Categories | Photos, prices, sale prices, sizes, stock, visibility |
| Home banners | The auto-playing slider at the top of the home page |
| Pages | Shipping, returns, privacy, terms, contact (rich text). Placeholders like `{phone}` and `{free_shipping_over}` are filled from Site settings |
| Site settings | Contact details, WhatsApp, Instagram, store address, shipping fee, free-shipping amount, COD on/off, Razorpay keys, announcement bar, slider speed |
| Customers | Accounts and admin access |

Values saved in **Site settings** override the defaults below.

### Using the admin on your phone

- Open `https://pachwomen.com/admin` in Chrome (Android) or Safari (iPhone) and choose **Add to Home screen**. It opens like an app with the PACH icon.
- **Orders** opens on the **To ship** tab. Tap an order for one-tap **Mark shipped** (with tracking number), **Mark delivered**, **WhatsApp customer**, **Call** and **Cancel**. The list refreshes itself every 30 seconds.
- Product photos can be taken straight from the phone camera; they are resized on the phone before uploading.

### Installable app (shop and admin)

Both `pachwomen.com` and `pachwomen.com/admin` install to a phone's home screen (Chrome menu → **Install app**, or Safari → Share → **Add to Home Screen**) and open full-screen.

- **Offline:** pages you have opened are saved and open without internet; unopened pages show an offline screen. Placing orders and saving changes still need internet.
- **Updates:** after each deploy, open apps show **"New version available — Update"**; one tap loads the latest. No app store involved.
- **Privacy:** logging out deletes the saved pages from that phone.
- Files: `resources/views/pwa/sw.blade.php` (served at `/sw.js`), `public/pwa.js`, `public/site.webmanifest`, `public/admin.webmanifest`, admin tab bar in `resources/views/filament/mobile-tab-bar.blade.php`.

### Razorpay webhook (recommended)

So payments are recorded even if a customer closes the browser right after paying:

1. Razorpay Dashboard → Account & Settings → **Webhooks** → Add new webhook.
2. URL: `https://pachwomen.com/payment/razorpay/webhook`. Events: **payment.captured** and **order.paid**. Choose a secret.
3. Paste the same secret into Admin → Site settings → **Razorpay Webhook Secret**.
4. In Razorpay payment settings, keep **automatic capture** on.

## Defaults (`.env`)

| Key | Meaning |
|---|---|
| `RAZORPAY_KEY`, `RAZORPAY_SECRET` | From the Razorpay dashboard. Online payment appears at checkout only when both are set. Start with `rzp_test_…` keys. |
| `SHOP_SHIPPING_FEE` | Flat shipping fee in ₹ (default 80) |
| `SHOP_FREE_SHIPPING_OVER` | Free shipping from this subtotal (default 1499) |
| `SHOP_COD_ENABLED` | `true` / `false` for Cash on Delivery |
| `SHOP_PHONE`, `SHOP_WHATSAPP`, `SHOP_EMAIL` | Contact details shown in the footer, contact page and WhatsApp button |

Store address, Instagram links and the list of states are in `config/shop.php`.
Policy page text (shipping, returns, privacy, terms, contact) is in `resources/views/policies/`.

## How orders work

- Customers must log in (or register) to check out.
- **COD:** the order is confirmed straight away and stock is reduced.
- **Razorpay:** the order waits as *pending* until the payment signature is verified, then becomes *confirmed / paid* and stock is reduced. If the customer closes the payment window, the order stays *pending* and unpaid.
- Setting an order to **Cancelled** in the admin puts its stock back.
- Add the courier tracking number on the order in the admin; customers see it under *My orders*.

## Live site (Hostinger)

pachwomen.com runs on the Hostinger Business plan: code in `~/domains/pachwomen.com/app`,
`public_html` is a symlink to `app/public`, PHP 8.4 (`/opt/alt/php84/usr/bin/php`), MySQL `u710748228_pachwomen`.

**Update the live site** after pushing to GitHub (run `npm run build` and commit `public/build` first if CSS/JS changed):

```bash
ssh -p 65002 u710748228@62.72.28.54 "bash ~/domains/pachwomen.com/app/deploy.sh"
```

Reset or add an admin login: `php artisan shop:admin you@example.com` (on the server, in the app folder).

## Deploy to Hostinger from scratch (shared hosting)

1. In hPanel create a MySQL database and user, and turn on SSH (Advanced → SSH Access).
2. On your computer run `npm run build` (the server does not need Node.js).
3. Upload the project (Git or File Manager) **outside** `public_html`, e.g. `~/pachwomen`, including `public/build`.
4. Point the domain at the app's `public` folder: either set the domain's document root to `~/pachwomen/public` in hPanel, or replace `public_html` with a symlink: `ln -s ~/pachwomen/public ~/public_html`.
5. Over SSH:
   ```bash
   cd ~/pachwomen
   composer install --no-dev --optimize-autoloader
   cp .env.example .env   # then edit it
   php artisan key:generate
   php artisan migrate --force
   php artisan db:seed --force   # first time only: admin user + sample products
   php artisan storage:link
   php artisan icons:cache
   php artisan optimize
   ```
6. In `.env` set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://pachwomen.com`,
   `DB_CONNECTION=mysql` plus the `DB_*` details, real `MAIL_*` SMTP settings (for password-reset emails),
   and the live Razorpay keys.
7. Log in at https://pachwomen.com/admin, change the admin password, delete the sample products
   and add real ones.
