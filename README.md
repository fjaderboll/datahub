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

## Usage
The IoT devices can for example do a simple HTTP request like this:
```
POST /api/nodes/my-house/readings
```
with this data:
```
temperature=22.5
humidity=30.3
```

This data can later be retrieved with:
```
GET /api/nodes/my-house/sensors/temperature/readings
```
returning:
```json
[
    { "value": 22.5, "timestamp": "2021-09-10T14:30:07Z" },
    { "value": 22.3, "timestamp": "2021-09-10T13:30:03Z" },
    { "value": 22.1, "timestamp": "2021-09-10T12:30:01Z" }
]
```

For more examples, see [api-examples.md](api-examples.md)
or read the Swagger documentation.

Automatic export to another system can also be set up.

## File structure
This project consist of two separate parts:

* The frontend (see [frontend/README.md](frontend/README.md)) is only for convenient management of users and tokens and for viewing data
* The backend (see [backend/README.md](backend/README.md)) is where it all happens
