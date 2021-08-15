#!/bin/bash

cd "$(dirname "$0")"

mkdir -p dist
rsync -avz --delete swagger-ui/dist/ dist/

pushd swagger-generator/vendor/zircote/swagger-php/bin/
./openapi ../../../../../rest/ -o ../../../../../rest/swagger.json
popd
cp rest/swagger.json dist/
