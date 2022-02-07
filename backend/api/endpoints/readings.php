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
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 @OA\Property(property="name", type="string"),
 *                 @OA\Property(property="desc", type="string"),
 *                 example={"name": "temperature", "desc": "in Celsius degrees"}
 *             )
 *         )
 *     ),
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=403, description="Not authorized"),
 *     @OA\Response(response=404, description="Sensor not found")
 * )
 */
registerEndpoint(Method::POST, Authorization::DEVICE, Operation::WRITE, "nodes/{nodeName}/sensors/{sensorName}/readings", function($nodeName, $sensorName) {
    createReading($nodeName, $sensorName);
    return "Reading created";
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
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=404, description="Sensor not found")
 * )
 */
registerEndpoint(Method::GET, Authorization::DEVICE, Operation::READ, "nodes/{nodeName}/sensors/{sensorName}/readings", function($nodeName, $sensorName) {
    return getReadings($nodeName, $sensorName);
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
 *     @OA\Response(response=404, description="Sensor not found")
 * )
 */
registerEndpoint(Method::DELETE, Authorization::DEVICE, Operation::WRITE, "nodes/{nodeName}/sensors/{sensorName}/readings/{id}", function($nodeName, $sensorName, $id) {
    $dbSensor = findSensor($nodeName, $sensorName);
    dbUpdate("DELETE FROM reading WHERE sensor_id = ? AND id = ?", $dbSensor['id'], $id);
    return "Deleted reading $id";
});

// ----------------------
function createReading($nodeName, $sensorName) {
    $value = getMandatoryRequestValue("value");
    $timestamp = getOptionalRequestValue("timestamp", date('c', time()));
    $offset = getOptionalRequestValue("offset", 0); // seconds

    $dbSensor = findSensor($nodeName, $sensorName);

    $unixTime = strtotime($timestamp);
    if($unixTime === false) {
        requestParameterFail("Invalid timestamp: $timestamp");
    }
    if(!is_numeric($offset)) {
        requestParameterFail("Invalid offset: $offset");
    }
    $unixTime += $offset;
    $validatedTimestamp = date('c', $unixTime);

    dbUpdate("INSERT INTO reading(sensor_id, value, timestamp) VALUES (?, ?, ?)", $dbSensor['id'], $value, $validatedTimestamp);
    return dbGetLastId();
}

function getReadings($nodeName, $sensorName) {
    $additionalSql = ' ORDER BY "timestamp" DESC ';

    $limit = getOptionalRequestValue("limit", "none");
    if($limit === "none") {
        $additionalSql .= "LIMIT -1";
    } else if(ctype_digit($limit)) {
        $additionalSql .= "LIMIT $limit";
    } else {
        $additionalSql .= "LIMIT 10";
    }

    if($sensorName != null) {
        $dbSensor = findSensor($nodeName, $sensorName);
        $dbReadings = dbQuery("SELECT * FROM e_reading WHERE sensor_id = ?".$additionalSql, $dbSensor['id']);
    } else if($nodeName != null) {
        $dbNode = findNode($nodeName);
        $dbReadings = dbQuery("SELECT * FROM e_reading WHERE node_id = ?".$additionalSql, $dbNode['id']);
    } else {
        $dbReadings = dbQuery("SELECT * FROM e_reading".$additionalSql);
    }

    $readings = array();
    foreach($dbReadings as $dbReading) {
		$reading = convertFromDbObject($dbReading, array('id', 'node_name', 'sensor_name', 'timestamp', 'value'));
        array_push($readings, $reading);
	}
    return $readings;
}
