<?php

/**
 * @OA\Post(
 *     path="/users",
 *     summary="Creates a new user",
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
    $username = getMandatoryRequestValue("username");
	$password = getMandatoryRequestValue("password");

	$passwordSalt = createPasswordSalt();
	$passwordHash = createPasswordHash($password, $passwordSalt);

    $userCount = dbQuerySingle("SELECT count(*) FROM users")[0];

	dbUpdate("
			INSERT INTO users(
				username,
				password_hash,
				password_salt,
                admin
			) VALUES (?, ?, ?, ?)
			",
			$username,
			$passwordHash,
			$passwordSalt,
            ($userCount == 0 ? 1 : 0)
	);
	return "User $username created";
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
    $dbUsers = dbQuery("SELECT * FROM users");
    $users = array();
    foreach($dbUsers as $dbUser) {
		$user = convertFromDbObject($dbUser, array('username', 'email', 'admin'));
        $user['admin'] = ($user['admin'] > 0);
		array_push($users, $user);
	}
    return $users;
});

registerEndpoint(Method::GET, Authorization::USER, "users/{username}", function($username) {
    $dbUser = dbQuerySingle("SELECT * FROM users WHERE id = ?", getUserId());
	$user = convertFromDbObject($dbUser, array('username', 'email', 'admin'));
    $user['admin'] = ($user['admin'] > 0);

	return $user;
});

registerEndpoint(Method::POST, Authorization::NONE, "users/{username}/login", function($username) {
    $password = getMandatoryRequestValue("password");
    $users = dbQuery("SELECT * FROM users WHERE username = ?", $username);
    if(count($users) == 1) {
        $user = $users[0];
        $passwordHash = createPasswordHash($password, $user['password_salt']);
        if($passwordHash == $user['password_hash']) {
            $token = createToken($username, $user['id'], $user['admin'] > 0);
            return jsonEncode(array(
                "token" => $token
            ));
        }
    }
    requestAuthFail("Invalid credentials");
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
