<?php
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	ini_set('intl.default_locale', 'sv-SE');

	$FAIL_DELAY = 1; // seconds

	$DATA_DIR = __DIR__."/../data";
	$CRYPT_KEY_FILE  = $DATA_DIR."/crypt.key";
	$DB_MAIN_FILE  = $DATA_DIR."/main.db";
	$DB_USER_DIR  = $DATA_DIR."/datasets/";

	$DB_SETUP_MAIN_SQL  = __DIR__."/../db/setup-main.sql";
	$DB_SETUP_USER_SQL  = __DIR__."/../db/setup-user.sql";

	$TIMESTAMP_FORMAT_DB = "Y-m-d H:i:s";
	$TIMESTAMP_FORMAT_JSON = "c";
	$TIMESTAMP_FORMAT_POST = "Y-m-d\TH:i:sO";
