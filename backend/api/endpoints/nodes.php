<?php

/**
 * @OA\Post(
 *     path="/nodes",
 *     summary="Create new node",
 *     @OA\RequestBody(
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 @OA\Property(property="name", type="string"),
 *                 @OA\Property(property="desc", type="string"),
 *                 example={"name": "garage", "desc": "Located in the ceiling"}
 *             )
 *         )
 *     ),
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=400, description="Invalid name"),
 *     @OA\Response(response=403, description="Not authorized")
 * )
 */
registerEndpoint(Method::POST, Authorization::DATASET, "nodes", function() {
    $name = strtolower(getMandatoryRequestValue("name"));
    $desc = getOptionalRequestValue("desc", null);

    verifyDatasetWrite();
    verifyValidName($name);

    dbUpdate("INSERT INTO node(dataset_id, name, desc) VALUES (?, ?, ?)", getDatasetId(), $name, $desc);

    return "Node $name created";
});

/**
 * @OA\Get(
 *     path="/nodes",
 *     summary="List all nodes",
 *     @OA\Response(response=200, description="OK")
 * )
 */
registerEndpoint(Method::GET, Authorization::DATASET, "nodes", function() {
    verifyDatasetRead();

    $dbNodes = dbQuery("SELECT * FROM node WHERE dataset_id = ?", getDatasetId());
    $nodes = array();
    foreach($dbNodes as $dbNode) {
		$node = convertFromDbObject($dbNode, array('name', 'desc'));
        array_push($nodes, $node);
	}
    return $nodes;
});

/**
 * @OA\Get(
 *     path="/nodes/{name}",
 *     summary="Retrieve node information",
 *     @OA\Parameter(
 *         description="Name of node.",
 *         in="path",
 *         name="name",
 *         required=true,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=404, description="Node not found")
 * )
 */
registerEndpoint(Method::GET, Authorization::DATASET, "nodes/{name}", function($name) {
    verifyDatasetRead();

    $dbNode = findNode($name);
    $node = convertFromDbObject($dbNode, array('name', 'desc'));

    $dbSensors = dbQuery("SELECT * FROM sensor WHERE node_id = ?", $dbNode['id']);
    $node['sensors'] = array();
    foreach($dbSensors as $dbSensor) {
        $sensor = convertFromDbObject($dbSensor, array('name', 'desc', 'unit'));
        array_push($node['sensors'], $sensor);
    }
    return $node;
});

/**
 * @OA\Put(
 *     path="/nodes/{name}",
 *     summary="Update node",
 *     @OA\Parameter(
 *         description="Name of node.",
 *         in="path",
 *         name="name",
 *         required=true,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\RequestBody(
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 @OA\Property(property="email", type="string"),
 *                 example={"name": "node2", "desc": "My second node"}
 *             )
 *         )
 *     ),
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=404, description="Node not found")
 * )
 */
registerEndpoint(Method::PUT, Authorization::DATASET, "nodes/{name}", function($name) {
    verifyDatasetWrite();
    $dbNode = findNode($name);

    $changes = 0;

    $desc = getOptionalRequestValue("desc", null);
    if($desc) {
        $changes += dbUpdate("UPDATE node SET desc = ? WHERE id = ?", $desc, $dbNode['id']);
    }

    $newName = getOptionalRequestValue("name", null);
    if($newName) {
        $changes += dbUpdate("UPDATE node SET name = ? WHERE id = ?", $newName, $dbNode['id']);
    }

    return ($changes > 0 ? "Node updated" : "Node not updated");
});

/**
 * @OA\Delete(
 *     path="/nodes/{name}",
 *     summary="Delete node",
 *     @OA\Parameter(
 *         description="Name of node.",
 *         in="path",
 *         name="name",
 *         required=true,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=404, description="Node not found")
 * )
 */
registerEndpoint(Method::DELETE, Authorization::DATASET, "nodes/{name}", function($name) {
    verifyDatasetWrite();
    $dbNode = findNode($name);
    dbUpdate("DELETE FROM reading WHERE sensor_id IN (SELECT id FROM sensor WHERE node_id = ?)", $dbNode['id']);
    dbUpdate("DELETE FROM sensor WHERE node_id = ?", $dbNode['id']);
    dbUpdate("DELETE FROM node WHERE id = ?", $dbNode['id']);

    return "Deleted node ".$dbNode['name'];
});

// ----------------------
function findNode($name) {
    $name = strtolower($name);
    $nodes = dbQuery("SELECT * FROM node WHERE dataset_id = ? AND name = ?", getDatasetId(), $name);
    if(count($nodes) == 0) {
        requestFail("Node not found", 404);
    } else {
        return $nodes[0];
    }
}
