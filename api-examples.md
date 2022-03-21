# API REST examples for IoT devices

## Pushing a single value
```
POST /api/nodes/my-house/sensors/temperature/readings
```

with this data (depending on preferred Content-type):
```
{ "value": 22.5 }    # Content-type: application/json
value=22.5           # Content-type: application/x-www-form-urlencoded
```

If the node and/or sensor does not exist they will be created.

## Pushing multiple values in same request

```
POST /api/nodes/my-house/readings
```

with this data (JSON works also like above):
```
temperature=22.5
humidity=34.0
electric-meter=123456
door-state=1
```

This will create a new reading for the 4 sensors 'temperature', 'humidity', 'electric-meter' and 'door-state'. If the node or sensors don't exist they will be created.

## Additional query parameters when pushing readings
* `timestamp` - ISO formatted timestamp that will be used for this reading. Defaults to now.
* `offset` - Adds this number of seconds to the timestamp. Defaults to `0`

```
POST /api/nodes/my-house/sensors/temperature/readings?offset=-30
POST /api/nodes/my-house/sensors/temperature/readings?timestamp=2021-12-01
POST /api/nodes/my-house/sensors/temperature/readings?timestamp=2021-12-01T15:01:14Z&offset=3600
```

## Retrieving readings
The readings can then be retrieved for a single sensor, an entire node or just all of them:
```
GET /api/nodes/my-house/sensors/temperature/readings
GET /api/nodes/my-house/readings
GET /api/readings
```

Returned as JSON array:
```json
[
    {
        "id": 238,
        "nodeName": "my-house",
        "sensorName": "temperature",
        "timestamp": "2022-02-12T21:41:47+01:00",
        "value": 21,
        "unit": "°C"
    },
    {
        "id": 237,
        "nodeName": "my-house",
        "sensorName": "humidity",
        "timestamp": "2022-02-12T21:41:47+01:00",
        "value": 34,
        "unit": "%"
    },
    {
        "id": 236,
        "nodeName": "my-house",
        "sensorName": "temperature",
        "timestamp": "2022-02-12T19:39:40+01:00",
        "value": 20,
        "unit": "°C"
    }
]
```

## Additional query parameters when retrieving readings
* `limit` - Limits the number of returned readings to this number. Use `0` to get all. Defaults to `10000`.
* `sort` - Sort readings based on *timestamp* before applying `limit`. Must be `asc` or `desc`. Defaults to `desc`.
* `maxAge` - Return all readings younger than this number of seconds.
* `minAge` - Return all readings older than this number of seconds.
* `before` - Only return readings before this ISO timestamp.
* `after` - Only return readings after this ISO timestamp.

```php
// get last temperature
GET /api/nodes/my-house/sensors/temperature/readings?limit=1
// get first temperature
GET /api/nodes/my-house/sensors/temperature/readings?limit=1&sort=asc
// get all temperatures
GET /api/nodes/my-house/sensors/temperature/readings?limit=0
// get all in the past 10 minutes for 'my-house'
GET /api/nodes/my-house/readings?maxAge=600
// get all in January
GET /api/readings?after=2022-01-01&before=2022-02-01
```

## Last readings
The last stored readings are also returned when loading nodes and sensors:

```php
GET /api/nodes/my-house/sensors/temperature
GET /api/nodes/my-house
```

Node data response example:

```json
{
  "name": "my-house",
  "desc": "Placed in the hallway",
  "sensors": [
    {
      "name": "temperature",
      "desc": null,
      "unit": "°C",
      "readingCount": 118,
      "lastReading": {
        "id": 238,
        "nodeName": "my-house",
        "sensorName": "temperature",
        "timestamp": "2022-03-21T20:30:18+00:00",
        "value": 33.7,
        "unit": "°C"
      }
    },
    {
      "name": "humidity",
      "desc": null,
      "unit": "%",
      "readingCount": 109,
      "lastReading": {
        "id": 237,
        "nodeName": "my-house",
        "sensorName": "humidity",
        "timestamp": "2022-03-21T20:30:18+00:00",
        "value": 1,
        "unit": "%"
      }
    }
  ]
}
```