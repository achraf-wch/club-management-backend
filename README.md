# Club Management Backend

Laravel 12 backend for the Club Management Platform. This service exposes the API used by the frontend for authentication, club operations, event management, ticketing, member workflows, notifications, Google login, and 2FA.

## Overview

The backend is responsible for:

- authenticating users with session-based auth
- managing global roles and club roles
- handling club, member, and event lifecycle operations
- generating and validating tickets and QR codes
- processing board requests and president approvals
- serving profile, notifications, Google auth, and 2FA features

## Core Features

- Role model with `admin`, `user`, and club-level roles such as `president`, `board`, and `member`
- Club APIs for creation, update, retrieval, statistics, and membership context
- Member management, including board and president assignments
- Event creation, updates, ticket assignment, recap uploads, and scan endpoints
- Request/approval workflow between board and president
- Google authentication with Socialite
- Two-factor authentication and recovery-code flows
- PDF and QR-code tooling for ticketing

## Stack

- PHP `^8.2`
- Laravel `^12`
- MySQL
- Laravel Socialite
- Pragmarx Google2FA
- Barryvdh DomPDF
- Simple QrCode / Bacon QR Code

## Main Structure

```text
app/
  Http/
    Controllers/
    Middleware/
  Models/
database/
routes/
  web.php
storage/
tests/
```

## Requirements

- PHP 8.2+
- Composer
- MySQL
- Node.js and npm

## Environment

Create your local environment file from `.env.example` and adjust at minimum:

```env
APP_NAME=ClubManagement
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cluver
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
QUEUE_CONNECTION=database
```

If you use Google login or other external integrations, add the matching provider credentials in `.env`.

## Installation

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan storage:link
```

If your project uses frontend assets inside the backend runtime flow:

```bash
npm install
npm run build
```

## Development

Run the API locally:

```bash
php artisan serve
```

The app will usually be available at:

```text
http://127.0.0.1:8000
```

You can also use the bundled Composer workflow:

```bash
composer run dev
```

## Useful Commands

```bash
php artisan migrate
php artisan migrate:fresh --seed
php artisan config:clear
php artisan cache:clear
php artisan route:list
php artisan test
composer run test
```

## Authentication Model

The backend uses session-based authentication under the `web` guard.

- `admin` manages platform-wide data
- `president` manages direct club operations and approvals
- `board` manages shared club operations and submits approval requests where needed
- `member` consumes member-facing functionality only

Club-specific access is enforced through middleware such as `club_role`.

## API Areas

Main API groups exposed by the backend include:

- authentication and session verification
- profile and account settings
- club and club-membership context
- members and presidents
- events and event recaps
- tickets and QR validation
- requests and validation workflow
- notifications
- Google auth and 2FA

The main route definitions live in [routes/web.php](./routes/web.php).

## Notes For Frontend Integration

- Default local API base URL expected by the frontend is `http://localhost:8000`
- Authenticated requests rely on cookies and `credentials: include`
- Some endpoints are role-sensitive even when the URL is shared

## Troubleshooting

If authentication works inconsistently:

- confirm frontend and backend are using the expected local URLs
- verify sessions/cookies are enabled and not blocked by browser policy
- clear config and route cache with `php artisan optimize:clear`

If images or uploaded files do not load:

- run `php artisan storage:link`
- verify files exist in `storage/app/public`

If database-driven features fail:

- confirm MySQL is running
- recheck `.env` database credentials
- run migrations again

## Production Checklist

- set `APP_ENV=production`
- set `APP_DEBUG=false`
- configure a real database and mail provider
- configure secure session/cookie settings
- generate and protect OAuth / 2FA secrets
- run migrations before deployment
- configure queue workers if background processing is used

## License

This project is provided for the Club Management Platform codebase. Adapt licensing details to your organization or final project policy.
