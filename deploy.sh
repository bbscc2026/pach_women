#!/usr/bin/env bash
# Update the live site after pushing to GitHub. Run on the server:
#   bash ~/domains/pachwomen.com/app/deploy.sh
set -euo pipefail

PHP=/opt/alt/php84/usr/bin/php
cd "$(dirname "$0")"

test "$(pwd -P)" = /home/u710748228/domains/pachwomen.com/app
test -f .env
test -f public/index.php
test -L public/storage
exec 9>storage/framework/deploy.lock
flock -n 9 || { echo 'Another deployment is running'; exit 1; }
git diff --quiet
git diff --cached --quiet
git fetch origin main
test "$(git rev-parse origin/main)" = "${DEPLOY_COMMIT:-$(git rev-parse origin/main)}"
previous=$(git rev-parse HEAD)
recover() {
    status=$?
    if [ "$status" -ne 0 ]; then
        echo "Deployment failed; restoring code $previous"
        git restore --source="$previous" --worktree -- .
        $PHP artisan optimize:clear || true
    fi
    $PHP artisan up || true
    exit "$status"
}
trap recover EXIT

$PHP artisan down --retry=30
git merge --ff-only origin/main
$PHP "$(command -v composer)" install --no-dev --optimize-autoloader --no-interaction
$PHP artisan migrate --force
$PHP artisan optimize
$PHP artisan icons:cache
$PHP artisan up
curl --fail --silent --show-error --max-time 30 https://pachwomen.com/up >/dev/null

echo "Deployed $(git log -1 --format='%h %s')"
