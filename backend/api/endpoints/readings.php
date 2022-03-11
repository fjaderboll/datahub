<?php

/**
 * @OA\Post(
 *     path="/nodes/{nodeName}/sensors/{sensorName}/readings",
 *     summary="Create new reading",
 *     @OA\Parameter(
 *         description="Name of node.",
 *         in="path",
 *         name="nodeName",
 *         required=true,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         description="Name of sensor.",
 *         in="path",
 *         name="sensorName",
 *         required=true,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\RequestBody(
 *         @OA\MediaType(
 *             mediaType="application/x-www-form-urlencoded",
 *             @OA\Schema(
 *                 @OA\Property(property="value", type="string")
 *             )
 *         )
 *     ),
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=403, description="Not authorized")
 * )
 */
registerEndpoint(Method::POST, Authorization::DEVICE, Operation::WRITE, "nodes/{nodeName}/sensors/{sensorName}/readings", function($nodeName, $sensorName) {
    $value = getMandatoryBodyValue("value");
    createReading($nodeName, $sensorName, $value);
    return "Reading created";
});

/**
 * @OA\Post(
 *     path="/nodes/{nodeName}/readings",
 *     summary="Create new readings for severals sensors at once.",
 *     @OA\Parameter(
 *         description="Name of node.",
 *         in="path",
 *         name="nodeName",
 *         required=true,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\RequestBody(
 *         @OA\MediaType(
 *             mediaType="application/x-www-form-urlencoded",
 *             @OA\Schema(
 *                 @OA\Property(property="sensor1", type="string"),
 *                 @OA\Property(property="sensor2", type="string")
 *             )
 *         )
 *     ),
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=403, description="Not authorized")
 * )
 */
registerEndpoint(Method::POST, Authorization::DEVICE, Operation::WRITE, "nodes/{nodeName}/readings", function($nodeName) {
    $values = getAllBodyValues();
    foreach($values as $name => $value) {
        createReading($nodeName, $name, $value);
    }
    return count($values)." readings created";
});

/**
 * @OA\Get(
 *     path="/nodes/{nodeName}/sensors/{sensorName}/readings",
 *     summary="List all readings on the sensor",
 *     @OA\Parameter(
 *         description="Name of node.",
 *         in="path",
 *         name="nodeName",
 *         required=true,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         description="Name of sensor.",
 *         in="path",
 *         name="sensorName",
 *         required=true,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         description="Limit result to this number of readings (or use 0 for all readings)",
 *         in="query",
 *         name="limit",
 *         required=false,
 *         @OA\Schema(type="number")
 *     ),
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=404, description="Sensor not found")
 * )
 */
registerEndpoint(Method::GET, Authorization::DEVICE, Operation::READ, "nodes/{nodeName}/sensors/{sensorName}/readings", function($nodeName, $sensorName) {
    return getReadings($nodeName, $sensorName);
});

/**
 * @OA\Get(
 *     path="/nodes/{nodeName}/readings",
 *     summary="List all readings from all sensors on the node",
 *     @OA\Parameter(
 *         description="Name of node.",
 *         in="path",
 *         name="nodeName",
 *         required=true,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         description="Limit result to this number of readings (or use 0 for all readings)",
 *         in="query",
 *         name="limit",
 *         required=false,
 *         @OA\Schema(type="number")
 *     ),
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=404, description="Node not found")
 * )
 */
registerEndpoint(Method::GET, Authorization::DEVICE, Operation::READ, "nodes/{nodeName}/readings", function($nodeName) {
    return getReadings($nodeName, null);
});

/**
 * @OA\Get(
 *     path="/readings",
 *     summary="List all readings from all sensors on all nodes",
 *     @OA\Parameter(
 *         description="Limit result to this number of readings (or use 0 for all readings)",
 *         in="query",
 *         name="limit",
 *         required=false,
 *         @OA\Schema(type="number")
 *     ),
 *     @OA\Response(response=200, description="OK")
 * )
 */
registerEndpoint(Method::GET, Authorization::DEVICE, Operation::READ, "readings", function() {
    return getReadings(null, null);
});

/**
 * @OA\Delete(
 *     path="/nodes/{nodeName}/sensors/{sensorName}/readings/{id}",
 *     summary="Delete reading",
 *     @OA\Parameter(
 *         description="Name of node.",
 *         in="path",
 *         name="nodeName",
 *         required=true,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         description="Name of sensor.",
 *         in="path",
 *         name="sensorName",
 *         required=true,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         description="ID of reading.",
 *         in="path",
 *         name="id",
 *         required=true,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=404, description="Reading not found")
 * )
 */
registerEndpoint(Method::DELETE, Authorization::DEVICE, Operation::WRITE, "nodes/{nodeName}/sensors/{sensorName}/readings/{id}", function($nodeName, $sensorName, $id) {
    $dbSensor = findSensor($nodeName, $sensorName);
    $changes = dbUpdate("DELETE FROM reading WHERE sensor_id = ? AND id = ?", $dbSensor['id'], $id);
    if($changes == 0) {
        requestFail("Reading not found", 404);
    }
    return "Deleted reading $id";
});

// ----------------------
function createReading($nodeName, $sensorName, $value) {
    $timestamp = getOptionalQueryValue("timestamp", date('c', time()));
    $offset = getOptionalQueryValue("offset", 0); // seconds

    $dbSensor = findOrCreateSensor($nodeName, $sensorName);

    $unixTime = strtotime($timestamp);
    if($unixTime === false) {
        requestParameterFail("Invalid timestamp 'timestamp': $timestamp");
    }
    if(!is_numeric($offset)) {
        requestParameterFail("Invalid integer 'offset': $offset");
    }
    $unixTime += $offset;
    $validatedTimestamp = date('c', $unixTime);

    dbUpdate("INSERT INTO reading(sensor_id, value, timestamp) VALUES (?, ?, ?)", $dbSensor['id'], $value, $validatedTimestamp);
    $readingId = dbGetLastId();
    cleanup();
    return $readingId;
}

function getReading($id) {
    if($id === null) {
        return null;
    } else {
        $dbReading = dbQuerySingle("SELECT * FROM e_reading WHERE id = ?", $id);
        $reading = convertFromDbObject($dbReading, array('id', 'node_name', 'sensor_name', 'timestamp', 'value', 'unit'));
        return $reading;
    }
}

function getReadings($nodeName, $sensorName) {
    $sql = 'SELECT * FROM e_reading';
    $params = array();

    if($sensorName != null) {
        $dbSensor = findSensor($nodeName, $sensorName);
        $sql .= " WHERE sensor_id = ?";
        array_push($params, $dbSensor['id']);
    } else if($nodeName != null) {
        $dbNode = findNode($nodeName);
        $sql .= " WHERE node_id = ?";
        array_push($params, $dbNode['id']);
    } else {
        $sql .= " WHERE 1 = 1";
    }

    $maxAge = getOptionalQueryValue("maxAge", null);
    if($maxAge !== null && $maxAge !== "") {
        if(ctype_digit($maxAge)) {
            $timestamp = date('c', time() - $maxAge);
            $sql .= ' AND "timestamp" > ? ';
            array_push($params, $timestamp);
        } else {
            requestParameterFail("Invalid positive integer 'maxAge': $maxAge");
        }
    }

    $minAge = getOptionalQueryValue("minAge", null);
    if($minAge !== null && $minAge !== "") {
        if(ctype_digit($minAge)) {
            $timestamp = date('c', time() - $minAge);
            $sql .= ' AND "timestamp" < ? ';
            array_push($params, $timestamp);
        } else {
            requestParameterFail("Invalid positive integer 'minAge': $minAge");
        }
    }

    $after = getOptionalQueryValue("after", null);
    if($after !== null && $after !== "") {
        $unixTime = strtotime($after);
        if($unixTime === false) {
            requestParameterFail("Invalid timestamp 'after': $after");
        }
        $timestamp = date('c', $unixTime);

        $sql .= ' AND "timestamp" > ? ';
        array_push($params, $timestamp);
    }

    $before = getOptionalQueryValue("before", null);
    if($before !== null && $before !== "") {
        $unixTime = strtotime($before);
        if($unixTime === false) {
            requestParameterFail("Invalid timestamp 'before': $before");
        }
        $timestamp = date('c', $unixTime);

        $sql .= ' AND "timestamp" < ? ';
        array_push($params, $timestamp);
    }

    $sort = strtolower(getOptionalQueryValue("sort", "desc"));
    if($sort !== "asc" && $sort !== "desc") {
        requestParameterFail("Invalid sort, must be 'asc' or 'desc' but is: $sort");
    }
    $sql .= ' ORDER BY "timestamp" '.$sort.' ';

    $limit = getOptionalQueryValue("limit", null);
    if($limit === null || $limit === "") {
        $sql .= "LIMIT 100";
    } else if(ctype_digit($limit)) {
        if($limit == 0) {
            $sql .= "LIMIT -1";
        } else {
            $sql .= "LIMIT $limit";
        }
    } else {
        requestParameterFail("Invalid positive integer 'limit': $limit");
    }

    $dbReadings = dbQuery($sql, ...$params);
    $readings = array();
    foreach($dbReadings as $dbReading) {
		$reading = convertFromDbObject($dbReading, array('id', 'node_name', 'sensor_name', 'timestamp', 'value', 'unit'));
        array_push($readings, $reading);
	}
    return $readings;
}

function cleanup() {
    global $MAX_READINGS;

    if(isRandom(1000)) {
        dbUpdate('DELETE FROM reading WHERE id IN (SELECT id FROM reading ORDER BY "timestamp" DESC LIMIT -1 OFFSET ?)', $MAX_READINGS);
    }
}
