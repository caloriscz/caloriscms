#!/bin/sh
set -e

cd /var/www/html

mkdir -p log temp/cache temp/sessions
chown -R www-data:www-data log temp www/files www/media www/pictures www/contacts www/links-media www/images 2>/dev/null || true

if [ ! -f vendor/autoload.php ]; then
    composer install --prefer-dist --no-interaction
fi

exec "$@"
