#!/usr/bin/env bash
# Update the live site after pushing to GitHub. Run on the server:
#   bash ~/domains/pachwomen.com/app/deploy.sh
set -euo pipefail

PHP=/opt/alt/php84/usr/bin/php
cd "$(dirname "$0")"

$PHP artisan down --retry=30 || true
git pull --ff-only
$PHP "$(command -v composer)" install --no-dev --optimize-autoloader --no-interaction
$PHP artisan migrate --force
$PHP artisan optimize
$PHP artisan icons:cache
$PHP artisan up

echo "Deployed $(git log -1 --format='%h %s')"
