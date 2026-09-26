#!/usr/bin/env sh
set -eu

if [ ! -f .env.production ]; then
  echo "Missing .env.production. Copy .env.production.example and set secure production values first."
  exit 1
fi

docker compose -f docker-compose.production.yml --env-file .env.production build
docker compose -f docker-compose.production.yml --env-file .env.production up -d mysql redis
docker compose -f docker-compose.production.yml --env-file .env.production run --rm app php artisan migrate --force
docker compose -f docker-compose.production.yml --env-file .env.production run --rm app php artisan optimize
docker compose -f docker-compose.production.yml --env-file .env.production up -d
docker compose -f docker-compose.production.yml --env-file .env.production ps
