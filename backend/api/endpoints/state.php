<?php

/**
 * @OA\Get(
 *     path="/state",
 *     summary="Requests backend state information",
 *     @OA\Response(response=200, description="OK")
 * )
 */
registerEndpoint(Method::GET, Authorization::NONE, Operation::READ, "state", function() {
    $userCount = dbQuerySingle("SELECT count(*) FROM user")[0];

    return array(
        "createFirstUserRequired" => ($userCount == 0),
        "createUserAllowed" => true
    );
});
