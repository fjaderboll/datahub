<?php

/**
 * @OA\Post(
 *     path="/datasets/{datasetName}/nodes",
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
registerEndpoint(Method::POST, Authorization::USER, "datasets/{datasetName}/nodes", function($datasetName) {
    $dbDataset = findDataset($datasetName);
    openDatabaseConnection($dbDataset['id']);
    return createNode();
});

/**
 * @OA\Get(
 *     path="/datasets/{datasetName}/nodes",
 *     summary="List all nodes",
 *     @OA\Response(response=200, description="OK")
 * )
 */
registerEndpoint(Method::GET, Authorization::USER, "datasets/{datasetName}/nodes", function($datasetName) {
    $dbDataset = findDataset($datasetName);
    openDatabaseConnection($dbDataset['id']);
    return getNodes();
});

/**
 * @OA\Get(
 *     path="/datasets/{datasetName}/nodes/{nodeName}",
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
registerEndpoint(Method::GET, Authorization::USER, "datasets/{datasetName}/nodes/{nodeName}", function($datasetName, $nodeName) {
    $dbDataset = findDataset($datasetName);
    openDatabaseConnection($dbDataset['id']);
    return getNode($nodeName);
});

/**
 * @OA\Put(
 *     path="/datasets/{datasetName}/nodes/{nodeName}",
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
registerEndpoint(Method::PUT, Authorization::USER, "datasets/{datasetName}/nodes/{nodeName}", function($datasetName, $nodeName) {
    $dbDataset = findDataset($datasetName);
    openDatabaseConnection($dbDataset['id']);
    return updateNode($nodeName);
});

/**
 * @OA\Delete(
 *     path="/datasets/{datasetName}/nodes/{nodeName}",
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
registerEndpoint(Method::DELETE, Authorization::USER, "datasets/{datasetName}/nodes/{nodeName}", function($datasetName, $nodeName) {
    $dbDataset = findDataset($datasetName);
    openDatabaseConnection($dbDataset['id']);
    return deleteNode($nodeName);
});
