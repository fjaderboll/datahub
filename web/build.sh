#!/bin/bash

cd "$(dirname "$0")"

npm run build

cd dist/angular-starter
tar czf ../web.tar.gz .

cd ../..
echo "Created file dist/web.tar.gz"
