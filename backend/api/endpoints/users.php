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
registerEndpoint(Method::POST, Authorization::NONE, Operation::WRITE, "users", function() {
    $username = strtolower(getMandatoryRequestValue("username"));
	$password = getMandatoryRequestValue("password");

    verifyValidName($username);

	$passwordSalt = createPasswordSalt();
	$passwordHash = createPasswordHash($password, $passwordSalt);

    $userCount = dbQuerySingle("SELECT count(*) FROM user")[0];

	dbUpdate("
			INSERT INTO user(
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
    $userId = dbGetLastId();
    initUserDatabase($userId);

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
registerEndpoint(Method::GET, Authorization::ADMIN, Operation::READ, "users", function() {
    $dbUsers = dbQuery("SELECT * FROM user");
    $users = array();
    foreach($dbUsers as $dbUser) {
		$user = convertFromDbObject($dbUser, array('username', 'email', 'admin'));
        $user['admin'] = toBoolean($user['admin']);
        $user['databaseSize'] = filesize(getUserDatabaseFilename($dbUser['id']));;

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
registerEndpoint(Method::GET, Authorization::USER, Operation::READ, "users/{username}", function($username) {
    $username = strtolower($username);
    if($username == getUsername() || isAdmin()) {
        $dbUser = findUser($username);
        $user = convertFromDbObject($dbUser, array('username', 'email', 'admin'));
        $user['admin'] = toBoolean($user['admin']);
        $user['databaseSize'] = filesize(getUserDatabaseFilename($dbUser['id']));;
            
        return $user;
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
registerEndpoint(Method::POST, Authorization::NONE, Operation::READ, "users/{username}/login", function($username) {
    $username = strtolower($username);
    $password = getMandatoryRequestValue("password");

    $users = dbQuery("SELECT * FROM user WHERE username = ?", $username);
    if(count($users) == 1) {
        $user = $users[0];
        $passwordHash = createPasswordHash($password, $user['password_salt']);
        if($passwordHash == $user['password_hash']) {
            $token = createUserToken($username, $user['id'], toBoolean($user['admin']));
            return jsonEncode(array(
                "username" => $username,
                "admin" => toBoolean($user['admin']),
                "token" => $token,
                "expire" => getUserExpire()
            ));
        }
    }
    requestAuthFail("Invalid credentials");
});

/**
 * @OA\Get(
 *     path="/users/{username}/impersonate",
 *     summary="Impersonate a user",
 *     @OA\Parameter(
 *         description="Username of user",
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
registerEndpoint(Method::GET, Authorization::ADMIN, Operation::READ, "users/{username}/impersonate", function($username) {
    $username = strtolower($username);
    $dbUser = findUser($username);

    $token = createUserToken($username, $dbUser['id'], $dbUser['admin'] > 0);
    return jsonEncode(array(
        "username" => $username,
        "admin" => $dbUser['admin'] > 0,
        "token" => $token,
        "expire" => getUserExpire()
    ));
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
registerEndpoint(Method::PUT, Authorization::USER, Operation::WRITE, "users/{username}", function($username) {
    $username = strtolower($username);
    if($username == getUsername() || isAdmin()) {
        $userCount = dbQuerySingle("SELECT count(*) FROM user WHERE username = ?", $username)[0];
        if($userCount != 1) {
            requestFail("User not found", 404);
        }

        $changes = 0;

        $email = getOptionalRequestValue("email", null);
        if($email !== null) {
            $changes += dbUpdate("UPDATE user SET email = ? WHERE username = ?", $email, $username);
        }

        $admin = getOptionalRequestValue("admin", null);
        if($admin !== null) {
            if(isAdmin()) {
                $changes += dbUpdate("UPDATE user SET admin = ? WHERE username = ?", toDbBoolean($admin), $username);
            } else {
                requestAuthFail("Not authorized");
            }
        }

        return ($changes > 0 ? "User updated" : "Nothing updated");
    }
    requestAuthFail("Not authorized");
});

/**
 * @OA\Delete(
 *     path="/users/{username}",
 *     summary="Delete user",
 *     @OA\Parameter(
 *         description="Username of user.",
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
registerEndpoint(Method::DELETE, Authorization::USER, Operation::WRITE, "users/{username}", function($username) {
    $dbUser = findUser($username);

	if($username == getUsername() || isAdmin()) {
		dbUpdate("DELETE FROM user WHERE id = ?", $dbUser['id']);

		if($username == getUsername()) {
			closeUserDatabase();
		}
		removeUserDatabase($dbUser['id']);

        return "Deleted user ".$dbUser['username'];
    }
    requestAuthFail("Not authorized");
});

// ----------------------
function findUser($username) {
    $username = strtolower($username);
    $users = dbQuery("SELECT * FROM user WHERE username = ?", $username);
    if(count($users) == 0) {
        requestFail("User not found", 404);
    } else {
        return $users[0];
    }
}
