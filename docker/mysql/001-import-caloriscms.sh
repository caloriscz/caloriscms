#!/bin/bash
set -e

mysql --protocol=socket -uroot -p"${MYSQL_ROOT_PASSWORD}" "${MYSQL_DATABASE}" <<'EOSQL'
SET FOREIGN_KEY_CHECKS=0;
SOURCE /docker-entrypoint-initdb.d/db_mysql.dump;
SET FOREIGN_KEY_CHECKS=1;
EOSQL
