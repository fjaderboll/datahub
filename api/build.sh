#!/bin/bash

cd "$(dirname "$0")"

./build-swagger.sh

mkdir -p dist
cd rest
tar czf ../dist/api.tar.gz \
    --exclude=vendor \
    --exclude=composer.json \
    --exclude=composer.lock \
    .

cd ..
echo "Created file dist/api.tar.gz"
