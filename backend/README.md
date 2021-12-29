
## Setup
```shell
sudo apt install composer
pushd swagger-generator/
composer install
popd
pushd rest/
composer install
popd
```

## (Re)generate swagger.json
```shell
./build-swagger.sh
```

Based on this tutorial: https://medium.com/@tatianaensslin/how-to-add-swagger-ui-to-php-server-code-f1610c01dc03

## Deploy
```shell
./build.sh
```

Then unzip the resulting `dist/api.tar.gz` on your PHP enabled web server.
Make sure the `data` directory is writeable by the PHP executor.
