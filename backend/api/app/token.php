<?php

$token_crypt_method = "aes128";
$token_crypt_key = null;
$token_crypt_iv = null;

function loadCryptionKeys() {
	global $CRYPT_KEY_FILE, $token_crypt_method, $token_crypt_key, $token_crypt_iv;

	if(!$token_crypt_key) {
		if(file_exists($CRYPT_KEY_FILE)) {
			$c = file_get_contents($CRYPT_KEY_FILE);
			list($token_crypt_key, $token_crypt_iv) = explode(',', $c);
		} else {
			$token_crypt_key = substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 32)), 0, 32);
			$token_crypt_iv_length = openssl_cipher_iv_length($token_crypt_method);
			$token_crypt_iv = openssl_random_pseudo_bytes($token_crypt_iv_length);
			file_put_contents($CRYPT_KEY_FILE, $token_crypt_key.','.$token_crypt_iv);
			chmod($CRYPT_KEY_FILE, 0600);
		}
	}
}

$tokenLengthDevice = 32;

$tokenAuth = Authorization::NONE;
$tokenUsername = null;
$tokenUserId = null;
$tokenExpire = null;
$tokenRead = false;
$tokenWrite = false;

function checkAuthAndConnectDb() {
	global $tokenAuth, $tokenUsername, $tokenUserId, $tokenExpire, $tokenRead, $tokenWrite, $tokenLengthDevice;

	$token = getHeaderToken();
	$tokenOk = false;
	if($token) {
		// try user token
		if(!$tokenOk) {
			try {
				$decoded = base64_decode($token);
				$json = decrypt($decoded);
				$userData = jsonDecode($json);
				if($userData) {
					$expire = strtotime($userData['expire']);
					$now = strtotime("now");
					if($expire > $now) {
						$tokenUsername = $userData['username'];
						$tokenUserId = $userData['id'];
						$tokenExpire = $userData['expire'];
						if($userData['admin']) {
							$tokenAuth = Authorization::ADMIN;
						} else {
							$tokenAuth = Authorization::USER;
						}
						$tokenRead = true;
						$tokenWrite = true;
						$tokenOk = true;
						openDatabaseConnection(true, $userData['id']);
					}
				}
			} catch(Exception $e) { }
		}

		// try device token
		if(!$tokenOk) {
			try {
				if(strlen($token) == $tokenLengthDevice) {
					$parts = explode('.', $token);
					if(count($parts) == 2) {
						$oid = $parts[0];
						$userId = obfuscateId($oid);

						openDatabaseConnection(false, $userId);
						$dbToken = dbQuerySingle("SELECT * FROM token WHERE token = ? AND enabled = ?", $token, 1);
						
						$tokenUserId = $userId;
						$tokenAuth = Authorization::DEVICE;
						$tokenRead = $dbToken['read'];
						$tokenWrite = $dbToken['write'];
						$tokenOk = true;
					}
				}
			} catch(Exception $e) {
				closeDatabaseConnection();
			}
		}
	}

	if(!$tokenOk) {
		$tokenAuth = Authorization::NONE;
		$tokenRead = true;
		$tokenWrite = true;
		openDatabaseConnection(true);
	}
}

function encrypt($data) {
	global $token_crypt_method, $token_crypt_key, $token_crypt_iv;
	loadCryptionKeys();
	return openssl_encrypt($data, $token_crypt_method, $token_crypt_key, 0, $token_crypt_iv);
}

function decrypt($data) {
	global $token_crypt_method, $token_crypt_key, $token_crypt_iv;
	loadCryptionKeys();
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


function createUserToken($username, $userId, $admin) {
	global $tokenUsername, $tokenUserId, $tokenExpire, $tokenAuth;

	$userData = array(
		"username" => $username,
		"id" => $userId,
		"admin" => $admin,
		"expire" => date('c', strtotime("+8 hours"))
	);
	$json = jsonEncode($userData);
	$encrypted = encrypt($json);
	$token = base64_encode($encrypted);

	// apply to current session
	$tokenUsername = $userData['username'];
	$tokenUserId = $userData['id'];
	$tokenExpire = $userData['expire'];
	if($userData['admin']) {
		$tokenAuth = Authorization::USER;
	} else {
		$tokenAuth = Authorization::ADMIN;
	}
	
	return $token;
}

function createDeviceToken($userId) {
	global $tokenLengthDevice;

	$oid = obfuscateId($userId);
	$random = bin2hex(random_bytes($tokenLengthDevice / 2));
	$token = substr($oid.".".$random, 0, $tokenLengthDevice);
	return $token;
}

function obfuscateId($id) {
	$mask = 10101;
	return $id ^ $mask; // will always be unique for every input number (and reversible using same method)
}

function isAdmin() {
	global $tokenAuth;
	return $tokenAuth == Authorization::ADMIN;
}

function getTokenAuth() {
	global $tokenAuth;
	return $tokenAuth;
}

function getUsername() {
	global $tokenUsername;
	return $tokenUsername;
}

function getUserExpire() {
	global $tokenExpire;
	return $tokenExpire;
}

function getUserId() {
	global $tokenId;
	return $tokenId;
}

function getRead() {
	global $tokenRead;
	return $tokenRead;
}

function getWrite() {
	global $tokenWrite;
	return $tokenWrite;
}
