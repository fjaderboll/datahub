<?php

/**
 * @OA\Post(
 *     path="/datasets/{name}/tokens",
 *     summary="Create new dataset token",
 *     @OA\RequestBody(
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 @OA\Property(property="enabled", type="boolean"),
 *                 @OA\Property(property="read", type="boolean"),
 *                 @OA\Property(property="write", type="boolean"),
 *                 @OA\Property(property="desc", type="string"),
 *                 example={"enabled": true, "read": false, "write": true, "desc": "Temperature sensors input"}
 *             )
 *         )
 *     ),
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=404, description="Dataset not found")
 * )
 */
registerEndpoint(Method::POST, Authorization::USER, "datasets/{name}/tokens", function($name) {
    $enabled = getOptionalRequestValue("enabled", 1);
    $read = getOptionalRequestValue("read", 1);
    $write = getOptionalRequestValue("write", 1);
    $desc = getOptionalRequestValue("desc", null);

    $dbDataset = findDataset($name);
    openDatabaseConnection($dbDataset['id']);

    $token = createDatasetToken($dbDataset['id']);
    dbUpdate("INSERT INTO dataset_token(dataset_id, token, enabled, read, write, desc) VALUES (?, ?, ?, ?, ?, ?)", $dbDataset['id'], $token, $enabled, $read, $write, $desc);

    return "Dataset token created";
});

/**
 * @OA\Get(
 *     path="/datasets/{name}/tokens",
 *     summary="List all dataset tokens",
 *     @OA\Response(response=200, description="OK")
 * )
 */
registerEndpoint(Method::GET, Authorization::USER, "datasets/{name}/tokens", function($name) {
    $dbDataset = findDataset($name);
    openDatabaseConnection($dbDataset['id']);

    $dbTokens = dbQuery("SELECT * FROM dataset_token WHERE dataset_id = ?", $dbDataset['id']);
    $tokens = array();
    foreach($dbTokens as $dbToken) {
		$token = convertFromDbObject($dbToken, array('id', 'token', 'enabled', 'read', 'write', 'desc'));
        $token['enabled'] = toBoolean($token['enabled']);
        $token['read'] = toBoolean($token['read']);
        $token['write'] = toBoolean($token['write']);
        array_push($tokens, $token);
	}
    return $tokens;
});

/**
 * @OA\Put(
 *     path="/datasets/{name}/token/{id}",
 *     summary="Update dataset token",
 *     @OA\Parameter(
 *         description="Name of dataset.",
 *         in="path",
 *         name="name",
 *         required=true,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         description="Id of token.",
 *         in="path",
 *         name="id",
 *         required=true,
 *         @OA\Schema(type="number")
 *     ),
 *     @OA\RequestBody(
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 @OA\Property(property="email", type="string"),
 *                 example={"enabled": false, "read": true, "write": false, "desc": "Data extraction"}
 *             )
 *         )
 *     ),
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=404, description="Dataset not found")
 * )
 */
registerEndpoint(Method::PUT, Authorization::USER, "datasets/{name}/tokens/{id}", function($name, $id) {
    $dbDatasetToken = findDatasetToken($name, $id);

    $changes = 0;

    $enabled = getOptionalRequestValue("enabled", null);
    if($enabled) {
        $changes += dbUpdate("UPDATE dataset_token SET enabled = ? WHERE id = ?", toDbBoolean($enabled), $id);
    }

    $read = getOptionalRequestValue("read", null);
    if($read) {
        $changes += dbUpdate("UPDATE dataset_token SET read = ? WHERE id = ?", toDbBoolean($read), $id);
    }

    $write = getOptionalRequestValue("write", null);
    if($write) {
        $changes += dbUpdate("UPDATE dataset_token SET write = ? WHERE id = ?", toDbBoolean($write), $id);
    }

    $desc = getOptionalRequestValue("desc", null);
    if($desc) {
        $changes += dbUpdate("UPDATE dataset_token SET desc = ? WHERE id = ?", $desc, $id);
    }

    return ($changes > 0 ? "Dataset token updated" : "Nothing updated");
});

/**
 * @OA\Delete(
 *     path="/datasets/{name}/tokens/{id}",
 *     summary="Delete dataset token",
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
registerEndpoint(Method::DELETE, Authorization::USER, "datasets/{name}/tokens/{id}", function($name, $id) {
    $dbDatasetToken = findDatasetToken($name, $id);

    dbUpdate("DELETE FROM dataset_token WHERE id = ?", $id);

    return "Deleted dataset token";
});

// ----------------------
function findDatasetToken($datasetName, $tokenId) {
    $dbDataset = findDataset($datasetName);
    openDatabaseConnection($dbDataset['id']);

    $datasetTokens = dbQuery("SELECT * FROM dataset_token WHERE dataset_id = ? AND id = ?", $dbDataset['id'], $tokenId);
    if(count($datasetTokens) == 0) {
        requestFail("Dataset token not found", 404);
    } else {
        return $datasetTokens[0];
    }
}
