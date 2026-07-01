#!/bin/sh
set -e

if [ ! -d vendor ]; then
    composer install --no-dev --no-interaction --no-progress
fi

echo "Waiting for the database..."
until pg_isready -h "${DB_HOST:-db}" -p "${DB_PORT:-5432}" -U "${DB_USER:-library}" >/dev/null 2>&1; do
    sleep 1
done
echo "Database is ready."

php bin/console cache:clear --no-interaction
php bin/console cache:warmup --no-interaction
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
php bin/console app:seed-books --no-interaction || true

chown -R www-data:www-data var

exec "$@"
