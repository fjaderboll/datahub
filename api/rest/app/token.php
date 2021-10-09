<?php

// TODO key should not be in git
// TODO switch to JWT

$token_crypt_method = "aes128";
$token_crypt_key = "eHke6jT6sZJhb1c0yI2JEgY6V9kQTYwZ";
//$token_crypt_iv_length = openssl_cipher_iv_length($token_crypt_method);
$token_crypt_iv = hex2bin('3B2A7D2864211336363339432B2D1827'); //openssl_random_pseudo_bytes($token_crypt_iv_length);

$authUser = null;
$datasetToken = null;

function encrypt($data) {
	global $token_crypt_method, $token_crypt_key, $token_crypt_iv;
	return openssl_encrypt($data, $token_crypt_method, $token_crypt_key, 0, $token_crypt_iv);
}

function decrypt($data) {
	global $token_crypt_method, $token_crypt_key, $token_crypt_iv;
	return openssl_decrypt($data, $token_crypt_method, $token_crypt_key, 0, $token_crypt_iv);
}

function getHeaderToken() {
	$headers = apache_request_headers();
	if(isset($headers['Authorization'])) {
		$parts = explode(' ', $headers['Authorization']);
		if(count($parts) == 2 && $parts[0] == "Bearer") {
			$token = $parts[1];
			return $token;
		}
	}
	return null;
}

function createUserToken($username, $id, $admin) {
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

function readUserProperty($property) {
	global $authUser;

	if(!$authUser) {
		$token = getHeaderToken();
		if($token) {
			try {
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
			} catch(Exception $e) { }
		}
	}
	if($authUser) {
		return $authUser[$property];
	}
	return null;
}

function createDatasetToken($datasetId) {
	$e = encrypt($datasetId);
	$token = uniqid(base64_encode($e).".");
	return $token;
}

function readDatasetProperty($property) {
	global $datasetToken;

	if(!$datasetToken) {
		$token = getHeaderToken();
		if($token) {
			try {
				$parts = explode('.', $token);
				if(count($parts) == 2) {
					$e = $parts[0];
					$datasetId = decrypt(base64_decode($e));

					openDatabaseConnection($datasetId);
					$datasetTokens = dbQuery("SELECT * FROM dataset_token WHERE dataset_id = ? AND token = ? AND enabled = ?", $datasetId, $token, 1);
				    if(count($datasetTokens) == 1) {
						$datasetToken = $datasetTokens[0];
					}
				}
			} catch(Exception $e) { }
		}
	}
	if($datasetToken) {
		return $datasetToken[$property];
	}
	return null;
}

function isUser() {
	return (getUserId() != null);
}

function isDataset() {
	return (getDatasetId() != null);
}

function isAdmin() {
	return readUserProperty("admin");
}

function getUsername() {
	return readUserProperty("username");
}

function getUserId() {
	return readUserProperty("id");
}

function getDatasetId() {
	return readDatasetProperty("dataset_id");
}
