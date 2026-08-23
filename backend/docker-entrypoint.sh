#!/bin/sh
set -e

if [ ! -f config/jwt/private.pem ]; then
    mkdir -p config/jwt
    openssl genrsa -aes256 -passout pass:"${JWT_PASSPHRASE}" -out config/jwt/private.pem 4096
    openssl rsa -in config/jwt/private.pem -passin pass:"${JWT_PASSPHRASE}" -pubout -out config/jwt/public.pem
    chown www-data:www-data config/jwt/*.pem
fi

php bin/console doctrine:schema:update --force --no-interaction

exec "$@"