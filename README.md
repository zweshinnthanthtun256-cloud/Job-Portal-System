# JobSphere

**Find the right job. Hire the right talent.**

JobSphere is a production-oriented job portal built with Laravel 12, React, Inertia.js, Tailwind CSS, and MySQL. It includes role-aware authentication, scored candidate profiles with languages and certifications, private PDF resumes, company membership and public brand pages, complete employer job management, application timelines and withdrawal, conflict-aware interview scheduling, in-app notifications, recommendations, reports, audit logging, administration analytics/settings, development seed data, and focused security tests.

## Requirements

- PHP 8.3 or newer with PDO MySQL, mbstring, OpenSSL, tokenizer, XML, cURL, and fileinfo
- Composer 2
- Node.js 20 or newer and pnpm 10+
- MySQL 8 or newer

## Local setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Create a MySQL database, then set `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in `.env`.

```bash
php artisan migrate --seed
pnpm install
pnpm run build
php artisan serve
```

For active frontend development, run `pnpm run dev`. For background work, run `php artisan queue:work --tries=3`. In production, run Laravel's scheduler every minute:

```cron
* * * * * cd /path/to/jobsphere && php artisan schedule:run >> /dev/null 2>&1
```

## Development accounts

The seeder creates these accounts only when `APP_ENV` is `local` or `testing`. Their development password is `password`.

- Administrator: `admin@jobsphere.test`
- Employer: `employer@jobsphere.test`
- Job seeker: `seeker@jobsphere.test`

Never use these credentials in production.

## Verification

```bash
php artisan test
pnpm run build
./vendor/bin/pint --test
```

The feature suite covers registration, employer company ownership, login/logout, job lifecycle operations, application withdrawal, duplicate applications, expired jobs, private resume access, company-member privileges, interview isolation and time conflicts, profile extensions, in-app notifications, admin authorization, and cross-company isolation. The CI workflow runs the suite against MySQL 8.4.

## Architecture and security

- Controllers authorize and delegate validated data; Form Requests hold input rules.
- Employer access is derived from the authenticated user's company membership on the server.
- A unique database constraint prevents duplicate applications even under concurrent requests.
- Public job queries include only published, non-expired jobs.
- Application state transitions are recorded in a history table.
- Queue jobs use `queued_jobs`, leaving the `jobs` table for recruitment data.
- Demo records are blocked outside local and testing environments.

CSRF protection, password hashing, parameter binding, session regeneration, login throttling, mass-assignment protection, and Inertia's escaped React rendering are enabled through Laravel and the application layer.

## Production notes

Set `APP_ENV=production`, `APP_DEBUG=false`, a strong generated `APP_KEY`, secure database credentials, and a real mail transport. Serve the app over HTTPS and never run the demo seeder in production.

Use [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) for the Docker/MySQL/Redis/Nginx deployment, queue supervision, SMTP configuration, and daily backups. Use [docs/ACCESSIBILITY.md](docs/ACCESSIBILITY.md) for the keyboard, screen-reader, zoom, contrast, and automated release checks.
