#!/bin/sh
set -e

hash="$(php -r 'echo password_hash("admin", PASSWORD_BCRYPT);')"

mysql -h db -u caloris -pcaloris caloriscms \
    -e "UPDATE users SET password='${hash}', state=1 WHERE username='admin';"

echo "Admin password reset to: admin"
