<?php

/**
 * @OA\Post(
 *     path="/users",
 *     summary="Create new user",
 *     @OA\RequestBody(
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 @OA\Property(property="username", type="string"),
 *                 @OA\Property(property="password", type="string"),
 *                 example={"username": "foo", "password": "bar"}
 *             )
 *         )
 *     ),
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=400, description="Invalid username")
 * )
 */
registerEndpoint(Method::POST, Authorization::NONE, "users", function() {
    $username = strtolower(getMandatoryRequestValue("username"));
	$password = getMandatoryRequestValue("password");

    verifyValidName($username);

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
 * @OA\Get(
 *     path="/users",
 *     summary="List all users. Requires admin privileges.",
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=403, description="Not authorized")
 * )
 */
registerEndpoint(Method::GET, Authorization::ADMIN, "users", function() {
    $dbUsers = dbQuery("SELECT * FROM users");
    $users = array();
    foreach($dbUsers as $dbUser) {
		$user = convertFromDbObject($dbUser, array('username', 'email', 'admin'));
        $user['admin'] = toBoolean($user['admin']);
		array_push($users, $user);
	}
    return $users;
});

/**
 * @OA\Get(
 *     path="/users/{username}",
 *     summary="Retrieve user information",
 *     @OA\Parameter(
 *         description="Username of user. Requires admin privileges for other users than your own.",
 *         in="path",
 *         name="username",
 *         required=true,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=403, description="Not authorized"),
 *     @OA\Response(response=404, description="User not found")
 * )
 */
registerEndpoint(Method::GET, Authorization::USER, "users/{username}", function($username) {
    $username = strtolower($username);
    if($username == getUsername() || isAdmin()) {
        $dbUser = dbQuerySingle("SELECT * FROM users WHERE username = ?", $username);
        if($dbUser) {
        	$user = convertFromDbObject($dbUser, array('username', 'email', 'admin'));
            $user['admin'] = toBoolean($user['admin']);
        	return $user;
        }
        requestFail("User not found", 404);
    }
    requestAuthFail("Not authorized");
});

/**
 * @OA\Post(
 *     path="/users/{username}/login",
 *     summary="Login",
 *     @OA\Parameter(
 *         description="Username of user",
 *         in="path",
 *         name="username",
 *         required=true,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\RequestBody(
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 @OA\Property(property="password", type="string"),
 *                 example={"password": "bar"}
 *             )
 *         )
 *     ),
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=403, description="Invalid credentials")
 * )
 */
registerEndpoint(Method::POST, Authorization::NONE, "users/{username}/login", function($username) {
    $username = strtolower($username);
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
 *     path="/users/{username}",
 *     summary="Update user",
 *     @OA\Parameter(
 *         description="Username of user. Requires admin privileges for other users than your own.",
 *         in="path",
 *         name="username",
 *         required=true,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\RequestBody(
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 @OA\Property(property="email", type="string"),
 *                 example={"email": "foo@bar.com", "admin": true}
 *             )
 *         )
 *     ),
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=403, description="Not authorized"),
 *     @OA\Response(response=404, description="User not found")
 * )
 */
registerEndpoint(Method::PUT, Authorization::USER, "users/{username}", function($username) {
    $username = strtolower($username);
    if($username == getUsername() || isAdmin()) {
        $userCount = dbQuerySingle("SELECT count(*) FROM users WHERE username = ?", $username)[0];
        if($userCount != 1) {
            requestFail("User not found", 404);
        }

        $changes = 0;

        $email = getOptionalRequestValue("email", null);
        if($email) {
            $changes += dbUpdate("UPDATE users SET email = ? WHERE username = ?", $email, $username);
        }

        $admin = getOptionalRequestValue("admin", null);
        if($admin) {
            if(isAdmin()) {
                $changes += dbUpdate("UPDATE users SET admin = ? WHERE username = ?", toDbBoolean($admin), $username);
            } else {
                requestAuthFail("Not authorized");
            }
        }

        return ($changes > 0 ? "User updated" : "Nothing updated");
    }
    requestAuthFail("Not authorized");
});
