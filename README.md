**NOTE**
This is very early in the development, so a lot left to happen before it will
be usuable.

**Initial TODOs**
* write endpoints for:
    * sensors
    * readings
    * dataset export
* extract crypt key to separate none versioned file
* write frontend

# Datahub

## About

### Background
I have many IoT devices generating data and need to store that data somewhere.
After searching I did not find any existing projects or free/cheap services
the offered the simplicity I was looking for so I decided to write my own
IoT database.

### Goal
The goal is a fast lightweight system that uses standard REST for storing
and retrieving data. Backend is written in PHP and frontend using Angular,
so this can easily be put on any web hotel or be self
hosted on your own webserver.

### Structure
...TODO create visual graph

### Data storage and separation
All user and setup data is stored in an SQLite3 database named `main.db`.

The dataset's data is stored in individual databases, improving
concurrent read and write since one dataset is not dependent on another dataset.

There are two kinds of tokens:
* *user token* - will give full access to all the user's datasets, but do expire. Mainly for administration.
* *dataset tokens* - can only read and/or write in the dataset they belong to, and do not expire, hence this is the token to be used in your IoT devices.

## Usage
The IoT device can do a simple HTTP request like this:
```
POST /rest/nodes/my-house/sensors/temp-outdoor/readings
POST /rest/nodes/my-house/sensors/temp-indoor/readings
POST /rest/nodes/garage/sensors/temp/readings
```
with this data:
```
{ "value": 22.5 }    # Content-type: application/json
value=22.5           # Content-type: application/x-www-form-urlencoded
```

This data can later be retrieved with:
```
GET /rest/nodes/my-house/sensors/temp-outdoor/readings
```
returning:
```json
[
    { "value": 22.5, "timestamp": "2021-09-10T14:30:07Z" },
    { "value": 22.3, "timestamp": "2021-09-10T13:30:03Z" },
    { "value": 22.1, "timestamp": "2021-09-10T12:30:01Z" }
]
```

Automatic export to another system can also be set up.

## File structure

* `web/` - frontend, see [web/README.md](web/README.md)
* `api/` - backend, see [api/README.md](api/README.md)

## Requirements
```shell
sudo apt install apache2 php php-sqlite3
```

## Setup
...TODO

## Future improvements
* Retention policy:
    * Per dataset
    * Maximum 1.000.000 entries (configurable)
    * Entry = nodes, sensors, readings, tokens
    * Every X:th (on average) POST request, delete readings to get below maximum
* Vacuum (every X:th request or something smarter)
* Dockerfile
* Add Swagger authorization annotation
* Group endpoints in Swagger (now everyone is in "default")
* Use proper JWT for user tokens
* Mobile friendly frontend
