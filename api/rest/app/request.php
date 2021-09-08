<?php

function handleRequest() {
    global $endpoints;

    http_response_code(500); // assume something fails, then change accordingly
	try {
		verifyEndpointExists();

		/*if($handler->isAuthRequired()) {
			verifyAuthorized();
		}*/

		//openDatabaseConnection();

		$response = executeEndpointFunction();

        //commitDatabaseConnection();

		http_response_code(200);
		echo $response;
	} catch(RequestException $e) {
		http_response_code($e->getStatus());
		echo $e->getMessage();
		sleep(2);
	} catch(Exception $e) {
		http_response_code(500);
		echo $e->getMessage();
		sleep(2);
	} finally {
		//closeDatabaseConnection();
	}
}
