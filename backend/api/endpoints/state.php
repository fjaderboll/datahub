<?php

/**
 * @OA\Get(
 *     path="/state",
 *     summary="Requests backend state information",
 *     @OA\Response(response=200, description="OK")
 * )
 */
registerEndpoint(Method::GET, Authorization::NONE, "state", function() {
    $userCount = dbQuerySingle("SELECT count(*) FROM users")[0];

    return jsonEncode(array(
        "createFirstUserRequired" => ($userCount == 0),
        "createUserAllowed" => true
    ));
});
