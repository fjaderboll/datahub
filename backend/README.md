# Datahub - REST API backend
The `api` root directory contains the `index.html` for the Swagger
documentation and a `.htaccess` file for redirecting all
non-Swagger requests to `main.php`.

## Data storage and separation
All user and setup data is stored in an SQLite3 database named `main.db`.

The dataset's data is stored in individual databases, improving
concurrent read and write since one dataset is never locking another dataset.

There are two kinds of tokens:
* *user token* - will give full access to all the user's datasets, but do expire. Mainly for administration.
* *dataset tokens* - can only read and/or write in the dataset they belong to, and do not expire, hence this is the token to be used in your IoT devices.

## Development setup

Swagger:

```shell
sudo apt install composer
pushd swagger-generator/
composer install
popd
pushd api/
composer install
popd
./build-swagger.sh
```

Install Apache (or Nginx or something else):

```shell
sudo apt install apache2 php php-sqlite3
ln -s `pwd`/api /var/www/html/datahub-api
```

Now navigate to `http://localhost/datahub-api/`

## (Re)generate swagger.json
You need to run this manually if you've updated the annotations in the code.

```shell
./build-swagger.sh
```

Based on this tutorial: https://medium.com/@tatianaensslin/how-to-add-swagger-ui-to-php-server-code-f1610c01dc03

## Build/package
This basically just zips the content of the `api` directory:

```shell
./build.sh
```

## Deployment
Unzip the resulting `dist/api.tar.gz` on your PHP enabled web server.
Make sure the `data` directory is writeable by the PHP executor.

## Future improvements
* Retention policy:
    * Per dataset
    * Maximum 1.000.000 entries (configurable)
    * Entry = nodes, sensors, readings, tokens
    * Every X:th (on average) POST request, delete readings to get below maximum
* Vacuum (every X:th request or something smarter)
* Add Swagger authorization annotation
* Group endpoints in Swagger (now everyone is in "default")
* Use proper JWT for user tokens
* Extract crypt key to separate none versioned file