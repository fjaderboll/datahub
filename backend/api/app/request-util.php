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
		$datas = getRequestDatas();

		foreach($datas as $data) {
			if(isset($data[$var]) && $data[$var] !== null) {
				return $data[$var];
			}
		}
		return $defaultValue;
	}

	function getAllRequestValues() {
		$datas = getRequestDatas();

		$values = array();
		foreach($datas as $data) {
			$dataKeys = array_keys($data);
			foreach($dataKeys as $key) {
				if($data[$key] !== null) {
					$values[$key] = $data[$key];
				}
			}
		}
		return $values;
	}

	function getRequestDatas() {
		$datas = array();

		if(isset($_SERVER["CONTENT_TYPE"]) && $_SERVER["CONTENT_TYPE"] == "application/json") {
			array_push($datas, jsonDecode(file_get_contents('php://input')));
		}
		
		// application/x-www-form-urlencoded
		$method = $_SERVER['REQUEST_METHOD'];
		if($method == Method::GET) {
			array_push($datas, $_GET);
		} else if($method == Method::POST) {
			array_push($datas, $_POST);
		} else if($method == Method::PUT) {
			parse_str(file_get_contents("php://input"), $data);
			array_push($datas, $data);
		} else {
			array_push($datas, $_REQUEST);
		}
		return $datas;
	}

