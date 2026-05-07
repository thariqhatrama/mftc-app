#!/usr/bin/env bash
# MFTC Production Deploy Script
# Run as deploy user inside /var/www/mftc

set -euo pipefail

cd /var/www/mftc

echo "==> Pulling latest code"
git pull origin main

echo "==> Installing PHP dependencies"
composer install --no-dev --optimize-autoloader

echo "==> Running database migrations"
php artisan migrate --force

echo "==> Caching config, routes, and views"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Restarting queue workers"
php artisan queue:restart

echo "==> Building frontend"
cd frontend
npm ci
npm run build
cd ..

echo "==> Reloading Nginx"
sudo systemctl reload nginx

echo "==> Deploy complete"
