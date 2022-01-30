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
registerEndpoint(Method::POST, Authorization::DEVICE, Operation::WRITE, "nodes", function() {
    $name = strtolower(getMandatoryRequestValue("name"));
    $desc = getOptionalRequestValue("desc", null);

    verifyValidName($name);

    dbUpdate("INSERT INTO node(name, desc) VALUES (?, ?)", $name, $desc);

    return "Node $name created";
});

/**
 * @OA\Get(
 *     path="/nodes",
 *     summary="List all nodes",
 *     @OA\Response(response=200, description="OK")
 * )
 */
registerEndpoint(Method::GET, Authorization::DEVICE, Operation::READ, "nodes", function() {
    $dbNodes = dbQuery("SELECT * FROM e_node");
    $nodes = array();
    foreach($dbNodes as $dbNode) {
		$node = convertFromDbObject($dbNode, array('name', 'desc', 'sensor_count', 'last_reading_timestamp'));
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
registerEndpoint(Method::GET, Authorization::DEVICE, Operation::READ, "nodes/{name}", function($name) {
    $dbNode = findNode($name);
    $node = convertFromDbObject($dbNode, array('name', 'desc'));
    $node['sensors'] = getSensors($dbNode['name']);
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
registerEndpoint(Method::PUT, Authorization::DEVICE, Operation::WRITE, "nodes/{name}", function($name) {
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
registerEndpoint(Method::DELETE, Authorization::DEVICE, Operation::WRITE, "nodes/{name}", function($name) {
    $dbNode = findNode($name);
    dbUpdate("DELETE FROM reading WHERE sensor_id IN (SELECT id FROM sensor WHERE node_id = ?)", $dbNode['id']);
    dbUpdate("DELETE FROM sensor WHERE node_id = ?", $dbNode['id']);
    dbUpdate("DELETE FROM node WHERE id = ?", $dbNode['id']);

    return "Deleted node ".$dbNode['name'];
});

// ----------------------
function findNode($name) {
    $name = strtolower($name);
    $nodes = dbQuery("SELECT * FROM node WHERE name = ?", $name);
    if(count($nodes) == 0) {
        requestFail("Node not found", 404);
    } else {
        return $nodes[0];
    }
}
