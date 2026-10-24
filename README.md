# HotSpotz

HotSpotz is a web platform for short-term accommodation rentals. Guests find places to stay and book them. Hosts list and manage their properties. Administrators moderate the platform.

It was built for the **LBAW** course (Laboratório de Bases de Dados e Aplicações Web) at FEUP, 3rd year, 1st semester (group lbaw2581).

🎬 A video demo is available at [`wikiAssets/lbaw2581.mp4`](wikiAssets/lbaw2581.mp4).

---

## Features

**Guests**
- Register, log in, and recover a password by email
- Search listings by location, dates and filters
- View listing pages with photos, availability calendar and reviews
- Book a stay and pay online (Stripe)
- Manage bookings: edit, cancel, repay
- Leave reviews, then edit or report them
- Save favourite listings
- Real-time chat with hosts
- Real-time notifications (Pusher)

**Hosts**
- Apply to become a host (an admin reviews the application)
- Create, edit and delete listings with photos and availability
- Accept or reject reservations and confirm payments
- Host dashboard with reservations and statistics
- Reply to reviews
- Premium subscription plans

**Administrators**
- Approve or reject host applications
- Search users, block or unblock them, and grant or revoke admin rights
- Handle reported reviews (dismiss or remove)

## Tech stack

| Layer      | Technology |
|------------|------------|
| Backend    | PHP 8.3, [Laravel 12](https://laravel.com/) |
| Database   | PostgreSQL 16 (schema `lbaw2581`) |
| Frontend   | Blade templates, Bootstrap 5, Sass, Tailwind, FullCalendar, Vite |
| Real-time  | Pusher + Laravel Echo |
| Payments   | Stripe |
| Dev tools  | Docker Compose (PostgreSQL + pgAdmin) |

## Project structure

```
app/                  Models, Controllers, Policies, Events, Mail
database/
  database-creation.sql   Schema (tables, triggers, indexes)
  database-populate.sql   Sample data
  lbaw-seed.sql           Combined script used by `php artisan db:seed`
  photos/                 Listing photos copied to storage when seeding
resources/views/      Blade templates (pages, partials, layouts, emails)
resources/sass, js/   Frontend sources compiled by Vite
routes/web.php        Web routes
a7_openapi.yaml       OpenAPI specification of the web resources
Dockerfile            Production image (PHP-FPM + Nginx)
docker-compose.yaml   Local PostgreSQL + pgAdmin
```

---

## Installation

### 1. Prerequisites

Install the following:

- [PHP](https://www.php.net/downloads) **8.3+** with the `mbstring`, `xml`, `pgsql` and `curl` extensions
- [Composer](https://getcomposer.org/) **2.2+**
- [Node.js](https://nodejs.org/) **18+** and npm
- [Docker Desktop](https://docs.docker.com/desktop/)

**macOS (Homebrew):**

```bash
brew install php@8.3 composer node
```

**Ubuntu 24.04+:**

```bash
sudo apt update
sudo apt install git composer php8.3 php8.3-mbstring php8.3-xml php8.3-pgsql php8.3-curl nodejs npm
```

On **Windows**, install PHP, Composer and Node natively and use Docker Desktop, or use WSL with Ubuntu 24.04 and follow the Ubuntu steps.

### 2. Clone the repository

```bash
git clone https://github.com/Atfas/FEUP-3Y1S-LBAW.git
cd FEUP-3Y1S-LBAW
```

### 3. Install dependencies

```bash
composer install
npm install
```

### 4. Configure the environment

The `.env` file is not in the repository, so create one in the project root. This minimal local configuration matches the Docker Compose database:

```dotenv
APP_NAME=HotSpotz
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=postgres
DB_SCHEMA=lbaw2581
DB_USERNAME=postgres
DB_PASSWORD=password

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=public

# Email (password recovery, notifications). Use "log" to write emails to storage/logs
MAIL_MAILER=log
MAIL_FROM_ADDRESS=noreply@hotspotz.com
MAIL_FROM_NAME="${APP_NAME}"

# Real-time chat and notifications (optional, https://pusher.com)
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=eu
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"

# Payments (optional, use Stripe test keys from https://dashboard.stripe.com/test/apikeys)
STRIPE_KEY=
STRIPE_SECRET=
```

Then generate the application key:

```bash
php artisan key:generate
```

> Payments need the Stripe keys. Live chat and notifications need the Pusher keys. The rest of the application runs without them.

---

## Running the project

### 1. Start the database

```bash
docker compose up -d
```

This starts:
- **PostgreSQL** on `localhost:5432` (user `postgres`, password `password`)
- **pgAdmin** at <http://localhost:4321>. To connect to the database, add a server with host `postgres`, user `postgres` and password `password`.

### 2. Create and seed the database

```bash
php artisan db:seed
php artisan storage:link
```

`db:seed` runs [`database/lbaw-seed.sql`](database/lbaw-seed.sql). It recreates the `lbaw2581` schema, loads the sample data and copies the listing photos into public storage. Run it again whenever you want to reset the data.

### 3. Build the frontend assets

For development with hot reload, in a separate terminal:

```bash
npm run dev
```

Or build once:

```bash
npm run build
```

### 4. Start the application

```bash
php artisan serve
```

Open <http://localhost:8000>.

### Test accounts

All seeded accounts use the password **`password123`**.

| Role  | Email |
|-------|-------|
| Admin | `admin@stay.com` |
| Admin | `super@stay.com` |
| Host (approved) | `isaac@host.com` |
| Host (pending application) | `david@host.com` |
| Guest | `alice@guest.com` |
| Guest | `bob@guest.com` |

### Stopping

Press `Ctrl+C` to stop `php artisan serve` and `npm run dev`. Then stop the containers:

```bash
docker compose down
```

---

## Running with Docker (production image)

The [`Dockerfile`](Dockerfile) builds a self-contained image with PHP-FPM and Nginx. It expects a `.env.production` file in the project root that points at a reachable PostgreSQL database.

```bash
npm run build
docker build -t hotspotz .
docker run -it -p 8000:80 --name hotspotz hotspotz
```

The application is then available at <http://localhost:8000>.

## Tests

```bash
php artisan test
```

---

## Documentation

- [`README_lbaw.md`](README_lbaw.md): the original LBAW Laravel template guide (code structure, configuration, publishing)
- [`a7_openapi.yaml`](a7_openapi.yaml): OpenAPI specification
- [`sitemap.png`](sitemap.png) and [`Hotspotz.drawio.svg`](Hotspotz.drawio.svg): sitemap and diagrams
- [`wikiAssets/`](wikiAssets): screenshots, accessibility and usability checklists, HTML/CSS validation reports

## Authors

Group **lbaw2581**, FEUP 2025/26.
