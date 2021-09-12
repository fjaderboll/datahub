<?php
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	ini_set('intl.default_locale', 'sv-SE');

	$DB_FILE  = __DIR__."/../data/main.db";
	$DB_SETUP_SQL  = __DIR__."/../data/setup-main.sql";

	$TIMESTAMP_FORMAT_DB = "Y-m-d H:i:s";
	$TIMESTAMP_FORMAT_JSON = "c";
	$TIMESTAMP_FORMAT_POST = "Y-m-d\TH:i:sO";
