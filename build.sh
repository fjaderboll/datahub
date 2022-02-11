#!/bin/bash -e

cd "$(dirname "$0")"

build() {
    project=$1
    filename=$2
    subpath=$3

    echo -n "Building $project..."
    mkdir -p dist/tmp/$subpath
    ./$project/build.sh > /dev/null 2>&1
    tar xzf $project/dist/$filename.tar.gz -C dist/tmp/$subpath
    echo "OK"
}

rm -fr dist/tmp
build backend api api
build frontend web .

cd dist/tmp
tar czf ../datahub.tar.gz .
cd ../..

rm -r dist/tmp
echo "Done, created file dist/datahub.tar.gz"
