# Institutional MoU & Partnership Tracking System

A Laravel application for managing institutional partners, agreements, approvals, obligations, controlled documents, renewals, operational alerts, reporting, and audit history.

## Requirements

- PHP 8.3 or newer
- Composer
- Node.js 20.19 or newer
- SQLite, MySQL, or PostgreSQL
- A configured mail transport for reminder email delivery

## Local setup

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

The prototype seed creates these accounts with password `password`:

| Role | Email |
|---|---|
| System Administrator | admin@example.com |
| Management | management@example.com |
| Legal / Review Officer | legal@example.com |
| Department Officer | officer@example.com |
| Read-only User | viewer@example.com |

These credentials are for local demonstrations only. Do not run `php artisan db:seed` with these accounts in production.

## Background tasks

The scheduler refreshes lifecycle indicators at 01:00 and sends reminder notifications at 08:00. Run it locally with:

```bash
php artisan schedule:work
```

Production should execute `php artisan schedule:run` every minute through cron. Configure `MAIL_*` values before enabling reminder delivery. Uploaded agreement documents use Laravel's private `local` disk and should remain outside the public web root.

## Validation

```bash
php artisan test
npm run build
composer lint:check
composer types:check
```

## Production checklist

- Set `APP_ENV=production`, `APP_DEBUG=false`, and the correct `APP_URL`.
- Use a production database and create a dedicated database account.
- Replace all prototype accounts and passwords.
- Configure SMTP or another supported mail transport.
- Configure the scheduler and, if queues are enabled, a supervised queue worker.
- Back up the database and private document storage together.
- Serve the application over HTTPS and restrict server access to `.env` and private storage.
- Run `php artisan optimize` after deployment.
