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
cd swagger-generator/vendor/zircote/swagger-php/bin/
./openapi ../../../../../rest/ -o ../../../../../rest/swagger.json
```
