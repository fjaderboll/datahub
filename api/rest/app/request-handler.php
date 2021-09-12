<?php

function handleRequest() {
    global $endpoints;

    http_response_code(500); // assume something fails, then change accordingly
    header('Content-type: text/plain; charset=utf-8');
	try {
		verifyEndpoint();

		openDatabaseConnection();
		$response = executeEndpoint();
        commitDatabaseConnection();

		http_response_code(200);
        if(is_array($response)) {
            header('Content-type: application/json; charset=utf-8');
            echo jsonEncode($response);
        } else {
            echo $response;
        }
	} catch(RequestException $e) {
		http_response_code($e->getStatus());
        echo $e->getMessage();
		sleep(2);
	} catch(Exception $e) {
		http_response_code(500);
        echo $e->getMessage();
		sleep(2);
	} finally {
		closeDatabaseConnection();
	}
}
