<?php

	function requestFail($message, $status = 500) {
		throw new RequestException($status, $message);
	}

	function requestParameterFail($message) {
		requestFail($message, 400);
	}

	function requestAuthFail($message) {
		requestFail($message, 403);
	}

	function getMandatoryRequestValue($var, $message = null) {
		$value = getOptionalRequestValue($var, null);
		if($value == null) {
			if($message != null) {
				requestParameterFail($message);
			} else {
				requestParameterFail("Missing request parameter \"$var\"");
			}
		} else {
			return $value;
		}
	}

	function getOptionalRequestValue($var, $defaultValue) {
		if(isset($_SERVER["CONTENT_TYPE"]) && $_SERVER["CONTENT_TYPE"] == "application/json") {
			$data = jsonDecode(file_get_contents('php://input'));
		} else { // application/x-www-form-urlencoded
			$method = $_SERVER['REQUEST_METHOD'];
			if($method == Method::GET) {
				$data = $_GET;
			} else if($method == Method::POST) {
				$data = $_POST;
			} else if($method == Method::PUT) {
				parse_str(file_get_contents("php://input"), $data);
			} else {
				$data = $_REQUEST;
			}
		}
		if(isset($data[$var]) && $data[$var] !== null) {
			return $data[$var];
		}
		return $defaultValue;
	}
