# Production deployment

JobSphere includes a repeatable Docker deployment with PHP 8.3, Nginx, MySQL 8.4, Redis, a supervised queue service, Laravel's scheduler, and daily database/upload backups.

1. Copy `.env.production.example` to `.env.production`, generate an application key with `php artisan key:generate --show`, and replace every placeholder credential.
2. Configure the SMTP host, username, password, verified sender, and production HTTPS URL. Keep `.env.production` outside version control.
3. Build and start the stack: `docker compose -f docker-compose.production.yml --env-file .env.production up -d --build`.
4. Run database migrations: `docker compose -f docker-compose.production.yml exec app php artisan migrate --force`.
5. Cache the framework configuration: `docker compose -f docker-compose.production.yml exec app php artisan optimize`.
6. Put a TLS terminator or managed load balancer in front of port 80 and enable health monitoring for `/`.

The `queue` container restarts automatically and processes queued mail. The `scheduler` container runs scheduled commands. The `backup` container writes a compressed MySQL dump and an upload archive every 24 hours to the `backups` volume, retaining 14 days by default. Copy backups to separate object storage and test restoration regularly.

To restore, stop application writes, decompress the selected database file into `mysql`, restore the upload archive into the `app-storage` volume, run `php artisan migrate --force`, and restart the app, queue, and scheduler services.

Before each release, run `php artisan test`, the MySQL CI workflow, `pnpm run build`, and `vendor/bin/pint --test`. After deployment, verify registration, email verification, password reset, application submission, interview email delivery, upload/download authorization, and queue health.
