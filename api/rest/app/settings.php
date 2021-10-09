<?php
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	ini_set('intl.default_locale', 'sv-SE');

	$FAIL_DELAY = 1; // seconds

	$DB_MAIN_FILE  = __DIR__."/../data/main.db";
	$DB_DATASET_DIR  = __DIR__."/../data/datasets/";

	$DB_SETUP_MAIN_SQL  = __DIR__."/../data/setup-main.sql";
	$DB_SETUP_DATASET_SQL  = __DIR__."/../data/setup-dataset.sql";

	$TIMESTAMP_FORMAT_DB = "Y-m-d H:i:s";
	$TIMESTAMP_FORMAT_JSON = "c";
	$TIMESTAMP_FORMAT_POST = "Y-m-d\TH:i:sO";
