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
POST /api/nodes/my-house/sensors/temp-outdoor/readings
POST /api/nodes/my-house/sensors/temp-indoor/readings
POST /api/nodes/garage/sensors/temp/readings
```
with this data:
```
{ "value": 22.5 }    # Content-type: application/json
value=22.5           # Content-type: application/x-www-form-urlencoded
```

This data can later be retrieved with:
```
GET /api/nodes/my-house/sensors/temp-outdoor/readings
```
returning:
```json
[
    { "value": 22.5, "timestamp": "2021-09-10T14:30:07Z" },
    { "value": 22.3, "timestamp": "2021-09-10T13:30:03Z" },
    { "value": 22.1, "timestamp": "2021-09-10T12:30:01Z" }
]
```

For more examples, see [examples.md](api-examples.md)
or read the Swagger documentation.

Automatic export to another system can also be set up.

## File structure
This project consist of two separate parts:

* The Angular [frontend](frontend/README.md) is only for convenient management of users and datasets and for viewing data
* The PHP [backend](backend/README.md) is where it all happens

## Future improvements

* Create Dockerfile
