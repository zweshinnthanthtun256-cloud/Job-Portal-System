#!/usr/bin/env bash
set -euo pipefail

# setup.sh
# Bootstraps a Laravel project in the current repo, installs Sail, Breeze (Inertia + Vue), and configures PostgreSQL.
# Run this on a machine with Docker installed.

echo "\n=== Laravel + Sail + Breeze (Inertia + Vue) + PostgreSQL bootstrap script ===\n"

# Safety: avoid overwriting existing project
if [ -f "composer.json" ]; then
  echo "composer.json already exists in this repo. Aborting to avoid overwriting an existing project.\n"
  echo "If you want to continue anyway, back up existing files and remove composer.json, then re-run this script."
  exit 1
fi

# Ensure Docker is available
if ! command -v docker >/dev/null 2>&1; then
  echo "Docker is required but not found in PATH. Install Docker and try again."
  exit 1
fi

# Create Laravel project using Composer
echo "Creating Laravel project (this will download files from packagist). This may take a few minutes..."
composer create-project --prefer-dist laravel/laravel .

# Copy .env example and generate app key
cp .env.example .env
php artisan key:generate

# Require Sail and install with PostgreSQL
composer require laravel/sail --dev
php artisan sail:install --with=pgsql,redis,mailhog

# Bring up Sail (build containers)
./vendor/bin/sail up -d

# Install Breeze (Inertia + Vue) inside Sail
./vendor/bin/sail composer require laravel/breeze --dev
./vendor/bin/sail artisan breeze:install vue

# Migrate the database and create storage link
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan storage:link

# Install npm dependencies and build assets
./vendor/bin/sail npm install
# For a production build use: ./vendor/bin/sail npm run build
./vendor/bin/sail npm run dev -- --host=0.0.0.0 --port=5173 &

cat <<'EOF'

Bootstrap complete.

Next steps:
- Open http://localhost to see the app (Sail exposes HTTP port by default).
- If you used Vite dev server, HMR runs on port 5173; Breeze detects dev mode automatically.
- Inspect generated files and commit them:
    git add .
    git commit -m "chore: bootstrap laravel app + sail + breeze(inertia/vue)"
    git push origin main

If you want me to create a feature branch and a PR with additional configuration (example .env values, README tweaks, CI/workflows), tell me and I will prepare it.

EOF
