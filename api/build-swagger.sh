#!/bin/bash

cd "$(dirname "$0")"

rsync -az --delete --exclude index.html swagger-ui/dist/ rest/swagger/

cd swagger-generator/vendor/zircote/swagger-php/bin/
./openapi ../../../../../rest/ -o ../../../../../rest/swagger.json
