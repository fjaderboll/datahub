<?php

/**
 * @OA\Get(
 *     path="/overview",
 *     summary="Summary of your nodes, sensors and latest readings",
 *     @OA\Response(response=200, description="OK")
 * )
 */
registerEndpoint(Method::GET, Authorization::DEVICE, Operation::READ, "overview", function() {
    $tokens = getTokens();

    $dbNodes = dbQuery("SELECT name, desc FROM node");
    $nodes = array();
    foreach($dbNodes as $dbNode) {
		$node = convertFromDbObject($dbNode, array('name', 'desc'));
        array_push($nodes, $node);
	}

    $dbSensors = dbQuery("SELECT * FROM e_sensor");
    $sensors = array();
    foreach($dbSensors as $dbSensor) {
		$sensor = convertFromDbObject($dbSensor, array('node_name', 'name', 'desc', 'unit', 'reading_count'));
        $sensor['lastReading'] = getReading($dbSensor['last_reading_id']);
        array_push($sensors, $sensor);
	}

    $dbReadings = dbQuery('SELECT * FROM e_reading ORDER BY "timestamp" DESC LIMIT 10');
    $lastReadings = array();
    foreach($dbReadings as $dbReading) {
		$reading = convertFromDbObject($dbReading, array('id', 'node_name', 'sensor_name', 'timestamp', 'value', 'unit'));
        array_push($lastReadings, $reading);
	}

    $overview = array(
        "tokens" => $tokens,
        "nodes" => $nodes,
        "sensors" => $sensors,
        "lastReadings" => $lastReadings
    );
    return $overview;
});

/**
 * @OA\Get(
 *     path="/overview/sensors",
 *     summary="Return all sensors including latest readings",
 *     @OA\Response(response=200, description="OK")
 * )
 */
registerEndpoint(Method::GET, Authorization::DEVICE, Operation::READ, "overview/sensors", function() {
    $dbSensors = dbQuery("SELECT * FROM e_sensor");
    $sensors = array();
    foreach($dbSensors as $dbSensor) {
		$sensor = convertFromDbObject($dbSensor, array('node_name', 'name', 'desc', 'unit', 'reading_count'));
        $sensor['lastReading'] = getReading($dbSensor['last_reading_id']);
        array_push($sensors, $sensor);
	}
    return $sensors;
});
