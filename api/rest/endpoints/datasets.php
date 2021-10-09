<?php

/**
 * @OA\Post(
 *     path="/datasets",
 *     summary="Create new dataset",
 *     @OA\RequestBody(
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 @OA\Property(property="name", type="string"),
 *                 @OA\Property(property="desc", type="string"),
 *                 example={"name": "my-dataset", "desc": "Aparament temperature and humidity"}
 *             )
 *         )
 *     ),
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=400, description="Invalid name")
 * )
 */
registerEndpoint(Method::POST, Authorization::USER, "datasets", function() {
    $name = strtolower(getMandatoryRequestValue("name"));
	$desc = getOptionalRequestValue("desc", null);

    verifyValidName($name);

    dbUpdate("INSERT INTO dataset(user_id, name, desc) VALUES (?, ?, ?)", getUserId(), $name, $desc);
    $datasetId = dbGetLastId();
    initDatasetDatabase($datasetId);

    return "Dataset $name created";
});

/**
 * @OA\Get(
 *     path="/datasets",
 *     summary="List all datasets",
 *     @OA\Response(response=200, description="OK")
 * )
 */
registerEndpoint(Method::GET, Authorization::USER, "datasets", function() {
    $dbDatasets = dbQuery("SELECT * FROM dataset WHERE user_id = ?", getUserId());
    $datasets = array();
    foreach($dbDatasets as $dbDataset) {
		$dataset = convertFromDbObject($dbDataset, array('name', 'desc'));
        $dataset['size'] = filesize(getDatasetFilename($dbDataset['id']));
        array_push($datasets, $dataset);
	}
    return $datasets;
});

/**
 * @OA\Get(
 *     path="/datasets/{name}",
 *     summary="Retrieve dataset information",
 *     @OA\Parameter(
 *         description="Name of dataset.",
 *         in="path",
 *         name="name",
 *         required=true,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=404, description="Dataset not found")
 * )
 */
registerEndpoint(Method::GET, Authorization::USER, "datasets/{name}", function($name) {
    $dbDataset = findDataset($name);
    $dataset = convertFromDbObject($dbDataset, array('name', 'desc'));
    $dataset['size'] = filesize(getDatasetFilename($dbDataset['id']));

    openDatabaseConnection($dbDataset['id']);

    $dbNodes = dbQuery("SELECT * FROM node WHERE dataset_id = ?", $dbDataset['id']);
    $dataset['nodes'] = array();
    foreach($dbNodes as $dbNode) {
        $node = convertFromDbObject($dbNode, array('name', 'desc'));

        $dbSensors = dbQuery("SELECT * FROM sensor WHERE node_id = ?", $dbNode['id']);
        $node['sensors'] = array();
        foreach($dbSensors as $dbSensor) {
            $sensor = convertFromDbObject($dbSensor, array('name', 'desc', 'unit'));
            array_push($node['sensors'], $sensor);
        }
        array_push($dataset['nodes'], $node);
    }
    return $dataset;
});

/**
 * @OA\Put(
 *     path="/datasets/{name}",
 *     summary="Update dataset",
 *     @OA\Parameter(
 *         description="Name of dataset.",
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
 *                 example={"name": "dataset2", "desc": "My second dataset"}
 *             )
 *         )
 *     ),
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=404, description="Dataset not found")
 * )
 */
registerEndpoint(Method::PUT, Authorization::USER, "datasets/{name}", function($name) {
    $dbDataset = findDataset($name);

    $changes = 0;

    $desc = getOptionalRequestValue("desc", null);
    if($desc) {
        $changes += dbUpdate("UPDATE dataset SET desc = ? WHERE id = ?", $desc, $dbDataset['id']);
    }

    $newName = getOptionalRequestValue("name", null);
    if($newName) {
        $changes += dbUpdate("UPDATE dataset SET name = ? WHERE id = ?", $newName, $dbDataset['id']);
    }

    return ($changes > 0 ? "Dataset updated" : "Nothing updated");
});

/**
 * @OA\Delete(
 *     path="/datasets/{name}",
 *     summary="Delete dataset",
 *     @OA\Parameter(
 *         description="Name of dataset.",
 *         in="path",
 *         name="name",
 *         required=true,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=404, description="Dataset not found")
 * )
 */
registerEndpoint(Method::DELETE, Authorization::USER, "datasets/{name}", function($name) {
    $dbDataset = findDataset($name);
    dbUpdate("DELETE FROM dataset WHERE id = ?", $dbDataset['id']);
    removeDatasetDatabase($dbDataset['id']);

    return "Deleted dataset ".$dbDataset['name'];
});

// ----------------------
function findDataset($name) {
    $name = strtolower($name);
    $datasets = dbQuery("SELECT * FROM dataset WHERE user_id = ? AND name = ?", getUserId(), $name);
    if(count($datasets) == 0) {
        requestFail("Dataset not found", 404);
    } else {
        return $datasets[0];
    }
}
