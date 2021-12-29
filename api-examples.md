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

Historical data can then be retrieved with:
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

This can then be retrieved for a single sensor with:
```
GET /api/nodes/my-house/sensors/electric-meter/readings
```
or for a the entire node:
```
GET /api/nodes/my-house/readings
```

returning:
```json
[
    { "sensor": "temperature", "value": 22.5, "timestamp": "2021-12-01T15:01:14Z" },
    { "sensor": "humidity", "value": 34.0, "timestamp": "2021-12-01T15:01:14Z" },
    { "sensor": "electric-meter", "value": 123456, "timestamp": "2021-12-01T15:01:14Z" },
    { "sensor": "door-state", "value": 1, "timestamp": "2021-12-01T15:01:14Z" }
]
```