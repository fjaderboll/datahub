<?php

/**
 * @OA\Post(
 *     path="/users",
 *     summary="Adds a new user - with oneOf examples",
 *     @OA\RequestBody(
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 @OA\Property(
 *                     property="id",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="name",
 *                     type="string"
 *                 ),
 *                 @OA\Property(
 *                     property="phone",
 *                     oneOf={
 *                     	   @OA\Schema(type="string"),
 *                     	   @OA\Schema(type="integer"),
 *                     }
 *                 ),
 *                 example={"id": "a3fb6", "name": "Jessica Smith", "phone": 12345678}
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="OK",
 *         @OA\JsonContent(
 *             oneOf={
 *                 @OA\Schema(ref="#/components/schemas/Result"),
 *                 @OA\Schema(type="boolean")
 *             }
 *         )
 *     )
 * )
 */
registerEndpoint(Method::POST, Authorization::NONE, "users", function() {
    return "user created";
});

/**
 * @OA\Put(
 *     path="/users/{id}",
 *     summary="Updates a user",
 *     @OA\Parameter(
 *         description="Parameter with mutliple examples",
 *         in="path",
 *         name="id",
 *         required=true,
 *         @OA\Schema(type="string"),
 *         @OA\Examples(example="int", value="1", summary="An int value."),
 *         @OA\Examples(example="uuid", value="0006faf6-7a61-426c-9034-579f2cfcfa83", summary="An UUID value."),
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="OK"
 *     )
 * )
 */
registerEndpoint(Method::GET, Authorization::ADMIN, "users", function() {
    return "user list";
});

registerEndpoint(Method::GET, Authorization::USER, "users/{username}", function($username) {
    return "user: $username";
});

/**
 * @OA\Put(
 *     path="/users/{id}",
 *     summary="Updates a user",
 *     @OA\Parameter(
 *         description="Parameter with mutliple examples",
 *         in="path",
 *         name="id",
 *         required=true,
 *         @OA\Schema(type="string"),
 *         @OA\Examples(example="int", value="1", summary="An int value."),
 *         @OA\Examples(example="uuid", value="0006faf6-7a61-426c-9034-579f2cfcfa83", summary="An UUID value."),
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="OK"
 *     )
 * )
 */
