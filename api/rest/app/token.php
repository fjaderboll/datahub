<?php

// TODO key should not be in git
// TODO switch to JWT

$token_crypt_method = "aes128";
$token_crypt_key = "eHke6jT6sZJhb1c0yI2JEgY6V9kQTYwZ";
//$token_crypt_iv_length = openssl_cipher_iv_length($token_crypt_method);
$token_crypt_iv = hex2bin('3B2A7D2864211336363339432B2D1827'); //openssl_random_pseudo_bytes($token_crypt_iv_length);

$authUser = null;

function encrypt($data) {
	global $token_crypt_method, $token_crypt_key, $token_crypt_iv;
	return openssl_encrypt($data, $token_crypt_method, $token_crypt_key, 0, $token_crypt_iv);
}

function decrypt($data) {
	global $token_crypt_method, $token_crypt_key, $token_crypt_iv;
	return openssl_decrypt($data, $token_crypt_method, $token_crypt_key, 0, $token_crypt_iv);
}

function createToken($username, $id, $admin) {
	global $authUser;

	$authUser = array(
		"username" => $username,
		"id" => $id,
		"admin" => $admin,
		"expire" => date('c', strtotime("+12 hours"))
	);
	$json = jsonEncode($authUser);
	$encrypted = encrypt($json);
	$encoded = base64_encode($encrypted);
	return $encoded;
}

function readTokenProperty($property) {
	global $authUser;

	if(!$authUser) {
		$headers = apache_request_headers();
		if(isset($headers['Authorization'])) {
			$parts = explode(' ', $headers['Authorization']);
			if(count($parts) == 2 && $parts[0] == "Bearer") {
				$token = $parts[1];
				$decoded = base64_decode($token);
				$json = decrypt($decoded);
				$user = jsonDecode($json);
				if($user) {
					$expire = strtotime($user['expire']);
					$now = strtotime("now");
					if($expire > $now) {
						$authUser = $user;
					}
				}
			}
		}
	}
	if($authUser) {
		return $authUser[$property];
	}
	return null;
}

function isAuthenticated() {
	return (readTokenProperty("id") != null);
}

function isAdmin() {
	return readTokenProperty("admin");
}

function getUsername() {
	return readTokenProperty("username");
}

function getUserId() {
	return readTokenProperty("id");
}
