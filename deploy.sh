#!/bin/bash -e

cd "$(dirname "$0")"

if [ ! -f dist/datahub.tar.gz ]; then
    echo "Run './build.sh' first"
    exit 1
fi

tar xzf dist/datahub.tar.gz -C /var/www/html/
