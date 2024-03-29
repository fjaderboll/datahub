#!/bin/bash -e

cd "$(dirname "$0")"

./build-swagger.sh

mkdir -p dist
cd api
composer install
tar czf ../dist/api.tar.gz \
    --exclude=composer.json \
    --exclude=composer.lock \
    --exclude=data/main.db \
    --exclude=data/crypt.key \
    --exclude=data/.gitkeep \
    --exclude=data/users \
    .

cd ..
echo "Created file dist/api.tar.gz"
