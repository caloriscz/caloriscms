#!/bin/sh
set -e

cd /var/www/html

mkdir -p www/log www/temp/cache www/temp/sessions
chown -R www-data:www-data www/log www/temp www/files www/media www/pictures www/contacts www/links-media www/images 2>/dev/null || true

if [ ! -f vendor/autoload.php ]; then
    composer install --prefer-dist --no-interaction
fi

exec "$@"
