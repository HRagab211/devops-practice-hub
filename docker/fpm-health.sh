#!/bin/sh
set -eu
SCRIPT_FILENAME=/var/www/html/public/index.php \
SCRIPT_NAME=/index.php REQUEST_URI=/up REQUEST_METHOD=GET \
SERVER_NAME=localhost SERVER_PORT=80 SERVER_PROTOCOL=HTTP/1.1 \
cgi-fcgi -bind -connect 127.0.0.1:9000 | grep -q 'Application up'
