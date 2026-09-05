#!/bin/sh
set -eu
cd /var/www/html
umask 022
mkdir -p /var/lib/hub storage/app/public storage/app/private storage/app/reports storage/logs
# touch never truncates an existing database; migrations remain an explicit action.
touch /var/lib/hub/database.sqlite
chown www-data:www-data /var/lib/hub /var/lib/hub/database.sqlite \
    storage/app/public storage/app/private storage/app/reports storage/logs
chmod 0750 /var/lib/hub storage/app/private storage/app/reports storage/logs
chmod 0640 /var/lib/hub/database.sqlite
chmod 0755 storage/app/public
exec gosu www-data "$@"
