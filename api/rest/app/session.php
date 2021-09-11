<?php
	session_start();

	function isLoggedIn() {
		return isset($_SESSION['username']);
	}

	function isAdmin() {
		return isLoggedIn() && isset($_SESSION['admin']) && $_SESSION['admin'];
	}

	function getUsername() {
		return $_SESSION['username'];
	}

	function verifyAuthorized($auth) {
		if($auth != Authorization::NONE) {
			if(!isLoggedIn()) {
				requestAuthFail("Not logged in");
			}
			if($auth != Authorization::ADMIN && !isAdmin()) {
				requestAuthFail("Not authorized");
			}
	    }
	}

	function login($username, $password) {
		$users = dbQuery("SELECT * FROM users WHERE username = ?", $username);
		if(count($users) != 1) {
			return false;
		}
		$user = $users[0];
		$passwordHash = createPasswordHash($password, $user['password_salt']);
		if($passwordHash == $user['password_hash']) {
			$_SESSION['username'] = $username;
			$_SESSION['user_id'] = $user['id'];
			return true;
		}
		return false;
	}

	function logout() {
		unset($_SESSION['username']);
		unset($_SESSION['user_id']);
	}
