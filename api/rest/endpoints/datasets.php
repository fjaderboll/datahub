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

    return "Dataset $name created";
});

/**
 * @OA\Get(
 *     path="/datasets",
 *     summary="List all datasets.",
 *     @OA\Response(response=200, description="OK")
 * )
 */
registerEndpoint(Method::GET, Authorization::USER, "datasets", function() {
    $dbDatasets = dbQuery("SELECT * FROM dataset WHERE user_id = ?", getUserId());
    $datasets = array();
    foreach($dbDatasets as $dbDataset) {
		$dataset = convertFromDbObject($dbDataset, array('name', 'desc'));
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
    $name = strtolower($name);
    $dbDataset = dbQuerySingle("SELECT * FROM dataset WHERE name = ?", $name);
    if($dbDataset) {
    	$dataset = convertFromDbObject($dbDataset, array('name', 'desc'));
        return $dataset;
    }
    requestFail("Dataset not found", 404);
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
    $name = strtolower($name);
    $datasetCount = dbQuerySingle("SELECT count(*) FROM dataset WHERE name = ?", $name)[0];
    if($datasetCount != 1) {
        requestFail("Dataset not found", 404);
    }

    $changes = 0;

    $desc = getOptionalRequestValue("desc", null);
    if($desc) {
        $changes += dbUpdate("UPDATE dataset SET desc = ? WHERE name = ?", $desc, $name);
    }

    $newName = getOptionalRequestValue("name", null);
    if($newName) {
        $changes += dbUpdate("UPDATE dataset SET name = ? WHERE name = ?", $newName, $name);
    }

    return ($changes > 0 ? "Dataset updated" : "Nothing updated");
});
