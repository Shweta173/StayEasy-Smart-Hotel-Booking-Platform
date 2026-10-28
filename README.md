# Hotel Booking Platform

A full-stack hotel booking platform. Guests can search properties, check room availability, hold rooms, and pay with Stripe. Hotel staff use a role-based admin panel to manage properties, rooms, pricing, bookings, invoices and reviews.

The project has two apps:

| Folder | What it is |
| --- | --- |
| [`hotel_booking/`](hotel_booking) | Laravel REST API and Blade admin panel |
| [`hotel-booking-frontend/`](hotel-booking-frontend) | React guest-facing website (plus a small React admin area) |

## Features

### For guests
- **Search and browse:** featured properties, search by location and dates, with filters for budget, star rating, guest review score and amenities.
- **Property pages:** image gallery with lightbox, amenities, room types and guest reviews.
- **Live availability and pricing:** availability calendar, multi-room bookings and real-time price updates, including VAT.
- **Booking holds:** selected rooms are held for 15 minutes with a countdown timer. Expired holds are released by a scheduled job.
- **Stripe checkout:** Stripe Elements payment form, with a webhook and status polling to confirm payment. Failed payments cancel the booking automatically.
- **Guest accounts:** register, log in (token auth with Laravel Sanctum), view and cancel bookings under *My Bookings*.
- **Invoices:** view invoices and download them as PDF.
- **Reviews:** post a review after a stay (5-star scale).
- **Emails:** booking confirmation and invoice emails.

### For hotel staff (admin panel)
- **Dashboard:** today's check-ins and check-outs, plus booking statistics.
- **Properties:** create and edit properties, with location validation and image upload (choose a primary image).
- **Room types, rate plans and amenities:** includes a pricing calendar for rate plans.
- **Availability:** availability calendar, blocked dates, and a view of active holds that staff can release.
- **Bookings:** filter bookings, change status, check guests in and out.
- **Orders, payments and invoices:** view each record and export invoices to PDF.
- **Guests and staff:** guest list with booking history, plus staff management.
- **Reviews moderation:** approve, reject, reply to or delete reviews.
- **Reports:** revenue and booking analytics.
- **Settings:** VAT rate and other platform settings.
- **Role-based access:** three roles, `admin`, `manager` and `front desk`, each with different permissions.

## Tech stack

**Backend:** PHP 8.3, Laravel 13, Laravel Sanctum, Stripe PHP SDK, DomPDF (`barryvdh/laravel-dompdf`), Blade templates for the admin panel, SQLite by default (MySQL supported)

**Frontend:** React 19, React Router 7, Axios, Stripe.js / React Stripe.js, date-fns, React Toastify, Create React App

**Architecture:** thin controllers backed by a service layer (`app/Services`), separate `Admin` and `Public` API controllers, role middleware, and a scheduled console command for expiring holds

## Project structure

```
hotel-booking-platform/
├── hotel_booking/                 # Laravel backend
│   ├── app/
│   │   ├── Console/Commands/      # bookings:expire-holds, email test commands
│   │   ├── Http/Controllers/
│   │   │   ├── Admin/             # Admin API + Blade admin panel
│   │   │   ├── Public/            # Guest-facing API
│   │   │   └── StripeWebhookController.php
│   │   ├── Http/Middleware/       # Admin auth and role checks
│   │   ├── Mail/                  # Booking confirmation, invoice email
│   │   ├── Models/
│   │   └── Services/              # Business logic
│   ├── database/migrations, seeders
│   ├── resources/views/admin/     # Admin panel (Blade)
│   └── routes/api.php, web.php, console.php
└── hotel-booking-frontend/        # React frontend
    └── src/
        ├── pages/                 # Search, property, rooms, checkout, bookings, invoices...
        ├── components/            # Stripe form, countdown timer, price summary...
        ├── contexts/              # Auth and toast contexts
        └── services/api.js        # Axios API client
```

## Running it locally

### Prerequisites
- PHP 8.3+ and Composer
- Node.js 18+ and npm
- A Stripe account in test mode (for payments)

### 1. Backend (Laravel API)

```bash
cd hotel_booking
composer install
cp .env.example .env
php artisan key:generate
```

Edit `.env` and add your Stripe test keys:

```
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

Create the database, run the migrations and seed demo data:

```bash
touch database/database.sqlite
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

The API runs at `http://127.0.0.1:8000`, and the Blade admin panel is at `http://127.0.0.1:8000/admin/login`.

To release expired booking holds automatically, run the scheduler in a second terminal:

```bash
php artisan schedule:work
```

On Windows you can use `run-scheduler.bat` with Task Scheduler instead.

To receive Stripe webhooks locally, use the [Stripe CLI](https://stripe.com/docs/stripe-cli):

```bash
stripe listen --forward-to 127.0.0.1:8000/api/webhooks/stripe
```

### 2. Frontend (React)

```bash
cd hotel-booking-frontend
npm install
cp .env.example .env
```

Set `REACT_APP_STRIPE_KEY` in `.env` to your Stripe publishable test key, then start the app:

```bash
npm start
```

The site runs at `http://localhost:3000`.

### Demo logins (from the seeder)

| Role | Email | Password |
| --- | --- | --- |
| Admin | admin@hotel.com | admin123 |
| Manager | manager@hotel.com | manager123 |
| Front desk | frontdesk@hotel.com | frontdesk123 |

These are local demo accounts only. Change them before deploying anywhere.

For Stripe test payments, use card `4242 4242 4242 4242` with any future expiry date and any CVC.

## API overview

| Area | Example endpoints |
| --- | --- |
| Public | `GET /api/public/properties/search`, `POST /api/public/availability/check`, `POST /api/public/bookings/pricing` |
| Guest (auth) | `POST /api/public/bookings/hold`, `POST /api/public/payment/intent`, `GET /api/public/guest/bookings`, `POST /api/public/reviews` |
| Admin (auth + role) | `/api/admin/properties`, `/api/admin/room-types`, `/api/admin/rate-plans`, `/api/admin/bookings`, `/api/admin/reviews` |
| Webhooks | `POST /api/webhooks/stripe` |

## Author

**Shweta Kamble** · [GitHub](https://github.com/Shweta173)
