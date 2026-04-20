# Building Management System (BMS)

A complete Laravel 11 building management system for landlords: rental management, utility tracking, invoicing, payment collection, maintenance, and dynamic reporting.

Built with zero build tooling — Tailwind and Chart.js load via CDN, SQLite is the default database. You can be running demo data locally within **2 minutes** of unzipping.

---

## ✨ Features

- 🔐 **Authentication** — single-landlord login, sessions, password hashing
- 🏢 **Buildings & Units** — multi-building support with floor, size, rent, and status tracking
- 👤 **Tenants & Leases** — tenant records tied to leases tied to units, with auto unit-status sync
- 📊 **Dashboard** — live KPIs, 6-month trend chart, payment method donut, invoice status distribution, overdue list
- 🧾 **Invoicing** — single-invoice creation or bulk monthly generation for all active leases, combining rent + utility readings
- 💵 **Payments** — record cash / bKash / Nagad / bank / card / etc. Auto-recalculates invoice balance and status
- ⚡ **Utilities** — configurable utility types (metered with rate × consumption, or flat fee), monthly readings
- 🛠 **Maintenance** — ticket tracking with priority, status, and cost
- 📈 **Reports**
  - **Collection** — monthly line chart + method breakdown
  - **Dues / Aging** — buckets by 1-30 / 31-60 / 61-90 / 90+ days
  - **Occupancy** — per-building stacked chart
  - **Utilities** — consumption and cost by type and month
- 🖨 **Printable invoices** — standalone clean layout, print-to-PDF ready
- 🇧🇩 **BDT by default** (৳) — easily changed via `.env`

---

## 🚀 Quick Start (2 minutes)

### Requirements

- **PHP 8.2+** with extensions: `pdo_sqlite`, `mbstring`, `openssl`, `bcmath`, `ctype`, `fileinfo`, `tokenizer`, `xml`, `curl`
- **Composer 2+**
- No Node.js, no database server required (SQLite is used by default)

### Installation

```bash
# 1. Unzip and enter the project
unzip bms.zip
cd bms

# 2. Install PHP dependencies
composer install

# 3. Create your .env file
cp .env.example .env

# 4. Generate app key
php artisan key:generate

# 5. Run migrations + seed demo data (creates SQLite DB automatically)
php artisan migrate --seed

# 6. Start the dev server
php artisan serve
```

Open **http://localhost:8000** in your browser.

### 🔑 Demo Login

| Email             | Password   |
| ----------------- | ---------- |
| `admin@bms.local` | `password` |

The seeder creates:
- 1 admin account
- 3 buildings (Dhaka + Chattogram) with 16 units
- 10 tenants, ~11 active leases
- 3–10 months of invoices per lease with realistic payment patterns
- Utility readings (electricity, water, gas, service charge)
- 5 maintenance tickets

---

## 🗄 Using MySQL or PostgreSQL

Edit `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bms
DB_USERNAME=root
DB_PASSWORD=yourpassword
```

Then:

```bash
php artisan migrate:fresh --seed
```

---

## 💱 Currency / Locale

Change in `.env`:

```env
BMS_CURRENCY=BDT
BMS_CURRENCY_SYMBOL=৳
BMS_DATE_FORMAT="d M Y"
```

Examples:
- USD: `BMS_CURRENCY=USD` · `BMS_CURRENCY_SYMBOL=$`
- INR: `BMS_CURRENCY=INR` · `BMS_CURRENCY_SYMBOL=₹`
- PKR: `BMS_CURRENCY=PKR` · `BMS_CURRENCY_SYMBOL=₨`

---

## 📂 Project Structure

```
app/
├── Http/Controllers/    # Auth, Dashboard, Building, Unit, Tenant,
│                        # Lease, Invoice, Payment, Utility, Report, Maintenance
├── Models/              # Eloquent models
├── Services/
│   └── InvoiceService   # Core business logic for invoice generation
└── Providers/
database/
├── migrations/          # All schema definitions
├── seeders/             # Demo data
└── database.sqlite      # Auto-created on first migration
resources/views/
├── layouts/app.blade.php  # Master layout with sidebar nav
├── auth/                  # Login
├── dashboard/             # Overview with charts
├── buildings/ units/ tenants/ leases/
├── invoices/              # Includes standalone print view
├── payments/ utilities/ maintenance/
└── reports/               # 4 interactive reports with Chart.js
routes/web.php
```

---

## 🔄 Core Workflows

### 1. Onboarding a new tenant

1. **Buildings → New Building** (if you haven't yet)
2. **Units → New Unit** — set the base rent
3. **Tenants → Add Tenant**
4. **Leases → New Lease** — links tenant to unit; unit is auto-marked `occupied`

### 2. Monthly billing cycle

1. (Optional) **Utilities → New Reading** — record electric/water meters for each unit
2. **Invoices → Generate Monthly Batch** — one click creates invoices for all active leases, combining rent + utilities
3. **Payments → Record Payment** — as tenants pay, record payments; invoice status auto-updates
4. **Reports → Collection / Dues** — see month-end performance

### 3. Tracking dues

The dashboard highlights overdue invoices in red. The **Dues & Aging** report breaks outstanding amounts into 0 / 1-30 / 31-60 / 61-90 / 90+ day buckets.

---

## 🧩 Architecture Notes

- **Invoice generation is idempotent** — running the monthly batch twice won't create duplicates
- **Payments auto-reconcile** — every payment create/update/delete triggers `Invoice::recalculateStatus()` which transitions the invoice through `unpaid → partial → paid`, and auto-marks `overdue` if past due date
- **Unit status is auto-managed** — creating a lease marks the unit `occupied`, ending a lease marks it `vacant`
- **Utility readings drive utility line-items** — metered types use `consumption × rate`, flat types use their fixed fee
- **Two invoice modes** — `single` (one lease, one month) or `batch` (all active leases for a month)

---

## 🔧 Production Deployment Tips

```bash
# Set environment to production
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Cache optimizations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Point your web server (Apache/Nginx) to the /public directory
# The .htaccess file handles URL rewriting for Apache
```

For Nginx:
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

---

## 🔐 Change Admin Password

```bash
php artisan tinker
>>> User::where('email', 'admin@bms.local')->update(['password' => Hash::make('your-new-password')]);
```

Or create a new admin user via Tinker:
```php
User::create([
    'name' => 'Your Name',
    'email' => 'you@example.com',
    'password' => Hash::make('secret'),
    'role' => 'admin',
]);
```

---

## 📝 License

MIT — use freely for commercial or personal projects.

---

Built with Laravel 11, Tailwind CSS, and Chart.js. No npm install required. ❤️
# bms
