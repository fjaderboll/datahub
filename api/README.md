https://medium.com/@tatianaensslin/how-to-add-swagger-ui-to-php-server-code-f1610c01dc03

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

## Generate swagger.json
```shell
./build-swagger.sh
```
