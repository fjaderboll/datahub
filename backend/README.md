# Datahub - REST API backend
The `api` root directory contains the `index.html` for the Swagger
documentation and a `.htaccess` file for redirecting all
non-Swagger requests to `main.php`.

## Data storage and separation
List of users and generic stuff is stored in an SQLite3 database named `main.db`.

The user's data (nodes, sensors, readings) is stored in individual databases, improving
concurrent read and write since different user's actions are never accessing the same database.

There are two kinds of tokens:
* *user token* - will give full access to all the user's data, but do expire. Mainly for administration.
* *device tokens* - can only read and/or write in their user's data, and do not expire, hence this is the token to be used in your IoT devices.

## Development setup

Configure Swagger:

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

Install Apache:

```shell
sudo apt install apache2 php php-sqlite3
sudo ln -s `pwd`/api /var/www/html/datahub-api
```

Modify `/etc/apache2/sites-enabled/000-default.conf` and add below section within the `<VirtualHost *:80>` section. The `AllowOverride All` allows for the use of the `.htaccess` file.
```
<Directory /var/www/html>
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```

Then enable the `RewriteEngine` module:
```shell
sudo a2enmod rewrite
sudo service apache2 restart
```

Make sure the `api/data` directory is writeable by the PHP executor.
```shell
sudo chgrp www-data api/data/
sudo chmod g+w api/data/
ls -ld api/data/
# drwxrwxr-x 3 fjaderboll www-data 4096 Jan 24 19:02 api/data/
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

### Patching database
Existing databases will not automatically get updates made in `setup-main.sql` and `setup-user.sql`.

```shell
cd api/data/users
sqlite3 1.db < patch.sql
sqlite3 2.db < patch.sql
```

## Future improvements
* Allow changing password
* Add Swagger authorization annotation
* Group endpoints in Swagger (now everyone is in "default")
* Use proper JWT for user tokens
