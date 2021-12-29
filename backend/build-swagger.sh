#!/bin/bash

cd "$(dirname "$0")"

rsync -az --delete --exclude index.html swagger-ui/dist/ api/swagger/

cd swagger-generator/vendor/zircote/swagger-php/bin/
./openapi ../../../../../api/ -o ../../../../../api/swagger.json
