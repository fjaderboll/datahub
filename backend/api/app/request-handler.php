<?php

function handleRequest() {
    global $FAIL_DELAY;

	header('Content-type: text/plain; charset=utf-8');

	$method = $_SERVER['REQUEST_METHOD'];
	header('Access-Control-Allow-Origin: http://localhost:4200');
	if($method == "OPTIONS") {
		header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
		header('Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, DELETE');
		http_response_code(204);
		return;
	}

    http_response_code(500); // assume something fails, then change accordingly
	$failed = true;
	try {
		checkAuthAndConnectDb();
		verifyEndpoint();

		$response = executeEndpoint();
        commitDatabaseConnection();

		http_response_code(200);
        if(is_array($response)) {
            header('Content-type: application/json; charset=utf-8');
            echo jsonEncode($response);
        } else {
            echo $response;
        }
		$failed = false;
	} catch(RequestException $e) {
		http_response_code($e->getStatus());
        echo $e->getMessage();
	} catch(Exception $e) {
		http_response_code(500);
        echo $e->getMessage();
	} finally {
		closeDatabaseConnection();
	}

	if($failed) {
		sleep($FAIL_DELAY); // sleep AFTER closing DB connection
	}
}
