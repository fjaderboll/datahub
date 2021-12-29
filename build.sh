#!/bin/bash -e

cd "$(dirname "$0")"

build() {
    name=$1
    path=$2

    echo -n "Building $name..."
    mkdir -p dist/tmp/$path
    ./$name/build.sh > /dev/null 2>&1
    tar xzf $name/dist/$name.tar.gz -C dist/tmp/$path
    echo "OK"
}

rm -fr dist/tmp
build web .
build api api

cd dist/tmp
tar czf ../datahub.tar.gz *
cd ..

echo "Done, created file dist/datahub.tar.gz"
