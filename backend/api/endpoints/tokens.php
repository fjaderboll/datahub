<?php

/**
 * @OA\Post(
 *     path="/tokens",
 *     summary="Create new token",
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
 *     @OA\Response(response=200, description="OK")
 * )
 */
registerEndpoint(Method::POST, Authorization::USER, Operation::WRITE, "tokens", function() {
    $enabled = getOptionalBodyValue("enabled", 1);
    $read = getOptionalBodyValue("read", 1);
    $write = getOptionalBodyValue("write", 1);
    $desc = getOptionalBodyValue("desc", null);

    $token = createDeviceToken(getUserId());
    dbUpdate("INSERT INTO token(token, enabled, read, write, desc) VALUES (?, ?, ?, ?, ?)", $token, toDbBoolean($enabled), toDbBoolean($read), toDbBoolean($write), $desc);

    return "Token created";
});

/**
 * @OA\Get(
 *     path="/tokens",
 *     summary="List all tokens",
 *     @OA\Response(response=200, description="OK")
 * )
 */
registerEndpoint(Method::GET, Authorization::USER, Operation::READ, "tokens", function() {
    return getTokens();
});

/**
 * @OA\Put(
 *     path="/tokens/{id}",
 *     summary="Update token",
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
 *     @OA\Response(response=404, description="Token not found")
 * )
 */
registerEndpoint(Method::PUT, Authorization::USER, Operation::WRITE, "tokens/{id}", function($id) {
    $dbToken = findToken($id);

    $changes = 0;

    $enabled = getOptionalBodyValue("enabled", null);
    if($enabled !== null) {
        $changes += dbUpdate("UPDATE token SET enabled = ? WHERE id = ?", toDbBoolean($enabled), $id);
    }

    $read = getOptionalBodyValue("read", null);
    if($read !== null) {
        $changes += dbUpdate("UPDATE token SET read = ? WHERE id = ?", toDbBoolean($read), $id);
    }

    $write = getOptionalBodyValue("write", null);
    if($write !== null) {
        $changes += dbUpdate("UPDATE token SET write = ? WHERE id = ?", toDbBoolean($write), $id);
    }

    $desc = getOptionalBodyValue("desc", null);
    if($desc !== null) {
        $changes += dbUpdate("UPDATE token SET desc = ? WHERE id = ?", $desc, $id);
    }

    return ($changes > 0 ? "Token updated" : "Nothing updated");
});

/**
 * @OA\Delete(
 *     path="/tokens/{id}",
 *     summary="Delete token",
 *     @OA\Parameter(
 *         description="Id of token.",
 *         in="path",
 *         name="id",
 *         required=true,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=404, description="Token not found")
 * )
 */
registerEndpoint(Method::DELETE, Authorization::USER, Operation::WRITE, "tokens/{id}", function($id) {
    $dbToken = findToken($id);

    dbUpdate("DELETE FROM token WHERE id = ?", $id);

    return "Token deleted";
});

// ----------------------
function findToken($tokenId) {
    $dbTokens = dbQuery("SELECT * FROM token WHERE id = ?", $tokenId);
    if(count($dbTokens) == 0) {
        requestFail("Token not found", 404);
    } else {
        return $dbTokens[0];
    }
}

function getTokens() {
    $dbTokens = dbQuery("SELECT * FROM token");
    $tokens = array();
    foreach($dbTokens as $dbToken) {
		$token = convertFromDbObject($dbToken, array('id', 'token', 'enabled', 'read', 'write', 'desc'));
        $token['enabled'] = toBoolean($token['enabled']);
        $token['read'] = toBoolean($token['read']);
        $token['write'] = toBoolean($token['write']);
        array_push($tokens, $token);
	}
    return $tokens;
}
