<?php

/**
 * @OA\Post(
 *     path="/nodes/{nodeName}/sensors",
 *     summary="Create new sensor",
 *     @OA\Parameter(
 *         description="Name of node.",
 *         in="path",
 *         name="nodeName",
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
 *     @OA\Response(response=400, description="Invalid name"),
 *     @OA\Response(response=403, description="Not authorized"),
 *     @OA\Response(response=404, description="Node not found")
 * )
 */
registerEndpoint(Method::POST, Authorization::DEVICE, Operation::WRITE, "nodes/{nodeName}/sensors", function($nodeName) {
    $dbNode = findNode($nodeName);
    $name = strtolower(getMandatoryBodyValue("name"));
    $desc = getOptionalBodyValue("desc", null);
    $unit = getOptionalBodyValue("unit", null);

	return createSensor($dbNode['id'], $name, $desc, $unit);
});

/**
 * @OA\Get(
 *     path="/nodes/{nodeName}/sensors",
 *     summary="List all sensors on the node",
 *     @OA\Parameter(
 *         description="Name of node.",
 *         in="path",
 *         name="nodeName",
 *         required=true,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=404, description="Node not found")
 * )
 */
registerEndpoint(Method::GET, Authorization::DEVICE, Operation::READ, "nodes/{nodeName}/sensors", function($nodeName) {
    return getSensors($nodeName);
});

/**
 * @OA\Get(
 *     path="/nodes/{nodeName}/sensors/{sensorName}",
 *     summary="Retrieve sensor information",
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
registerEndpoint(Method::GET, Authorization::DEVICE, Operation::READ, "nodes/{nodeName}/sensors/{sensorName}", function($nodeName, $sensorName) {
    $dbSensor = findSensor($nodeName, $sensorName);
    $dbESensor = dbQuerySingle("SELECT * FROM e_sensor WHERE id = ?", $dbSensor['id']);
    $sensor = convertFromDbObject($dbESensor, array('name', 'desc', 'unit', 'reading_count'));
    $sensor['lastReading'] = getReading($dbESensor['last_reading_id']);
    return $sensor;
});

/**
 * @OA\Put(
 *     path="/nodes/{nodeName}/sensors/{sensorName}",
 *     summary="Update sensor",
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
 *                 @OA\Property(property="email", type="string"),
 *                 example={"name": "sensor3", "desc": "My third sensor"}
 *             )
 *         )
 *     ),
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=404, description="Sensor not found")
 * )
 */
registerEndpoint(Method::PUT, Authorization::DEVICE, Operation::WRITE, "nodes/{nodeName}/sensors/{sensorName}", function($nodeName, $sensorName) {
    $dbSensor = findSensor($nodeName, $sensorName);

    $changes = 0;

    $desc = getOptionalBodyValue("desc", null);
    if($desc !== null) {
        $changes += dbUpdate("UPDATE sensor SET desc = ? WHERE id = ?", $desc, $dbSensor['id']);
    }

    $unit = getOptionalBodyValue("unit", null);
    if($unit !== null) {
        $changes += dbUpdate("UPDATE sensor SET unit = ? WHERE id = ?", $unit, $dbSensor['id']);
    }

    $newName = getOptionalBodyValue("name", null);
    if($newName !== null) {
        $changes += dbUpdate("UPDATE sensor SET name = ? WHERE id = ?", $newName, $dbSensor['id']);
    }

    return ($changes > 0 ? "Sensor updated" : "Sensor not updated");
});

/**
 * @OA\Delete(
 *     path="/nodes/{nodeName}/sensors/{sensorName}",
 *     summary="Delete node",
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
registerEndpoint(Method::DELETE, Authorization::DEVICE, Operation::WRITE, "nodes/{nodeName}/sensors/{sensorName}", function($nodeName, $sensorName) {
    $dbSensor = findSensor($nodeName, $sensorName);
    dbUpdate("UPDATE node SET last_reading_id = null WHERE id = ?", $dbSensor['node_id']);
    dbUpdate("UPDATE sensor SET last_reading_id = null WHERE id = ?", $dbSensor['id']);
    dbUpdate("DELETE FROM reading WHERE sensor_id = ? ", $dbSensor['id']);
    dbUpdate("DELETE FROM sensor WHERE id = ?", $dbSensor['id']);

    readingAggregateNode($nodeName);

    return "Deleted sensor ".$dbSensor['name'];
});

// ----------------------
function createSensor($nodeId, $name, $desc, $unit) {
	$name = strtolower($name);
	verifyValidName($name);

    dbUpdate("INSERT INTO sensor(node_id, name, desc, unit) VALUES (?, ?, ?, ?)", $nodeId, $name, $desc, $unit);

    return "Sensor $name created";
}

function findSensor($nodeName, $sensorName) {
    $nodeName = strtolower($nodeName);
    $sensorName = strtolower($sensorName);

    $dbNode = findNode($nodeName);
    $dbSensors = dbQuery("SELECT * FROM sensor WHERE node_id = ? AND name = ?", $dbNode['id'], $sensorName);
    if(count($dbSensors) == 0) {
        requestFail("Sensor not found", 404);
    } else {
        return $dbSensors[0];
    }
}

function findOrCreateSensor($nodeName, $sensorName) {
    $nodeName = strtolower($nodeName);
    $sensorName = strtolower($sensorName);

    $dbNode = findOrCreateNode($nodeName);
    $dbSensors = dbQuery("SELECT * FROM sensor WHERE node_id = ? AND name = ?", $dbNode['id'], $sensorName);
    if(count($dbSensors) == 0) {
        createSensor($dbNode['id'], $sensorName, null, null);
		$dbSensors = dbQuery("SELECT * FROM sensor WHERE node_id = ? AND name = ?", $dbNode['id'], $sensorName);
    }
    return $dbSensors[0];
}

function getSensors($nodeName) {
    $dbNode = findNode($nodeName);
    $dbSensors = dbQuery("SELECT * FROM e_sensor WHERE node_id = ?", $dbNode['id']);
    $sensors = array();
    foreach($dbSensors as $dbSensor) {
		$sensor = convertFromDbObject($dbSensor, array('name', 'desc', 'unit', 'reading_count'));
        $sensor['lastReading'] = getReading($dbSensor['last_reading_id']);
        array_push($sensors, $sensor);
	}
    return $sensors;
}
