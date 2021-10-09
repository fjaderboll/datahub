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
