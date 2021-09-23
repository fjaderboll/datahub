**NOTE**
This is very early in the development, so a lot left to happen before it will
be usuable.

# Datahub

## About
...

### Data storage and separation
All user and setup data is stored in an SQLite3 database named `main.db`.

The dataset's data is stored in individual databases, improving
concurrent read and write since one dataset is not dependent on another dataset.
(other than shared disk/memory/cpu stuff).

A user token will give full access to all the users datasets, while the dataset
tokens only can read and write from the dataset they belong to.

## Usage
...with examples

## File structure

* `web/` - frontend, see [web/README.md](web/README.md)
* `api/` - backend, see [api/README.md](api/README.md)

## Requirments
```shell
sudo apt install apache2 php php-sqlite3
```

## Setup
...

## TODO
* Dockerfile
* Proper Swagger authorization annotation
