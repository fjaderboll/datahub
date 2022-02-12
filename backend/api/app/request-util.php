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

	function getMandatoryQueryValue($var, $message = null) {
		$value = getOptionalQueryValue($var, null);
		if($value == null) {
			if($message != null) {
				requestParameterFail($message);
			} else {
				requestParameterFail("Missing query parameter \"$var\"");
			}
		} else {
			return $value;
		}
	}

	function getMandatoryBodyValue($var, $message = null) {
		$value = getOptionalBodyValue($var, null);
		if($value == null) {
			if($message != null) {
				requestParameterFail($message);
			} else {
				requestParameterFail("Missing ".$_SERVER['REQUEST_METHOD']." parameter \"$var\"");
			}
		} else {
			return $value;
		}
	}

	function getOptionalQueryValue($var, $defaultValue) {
		return getOptionalValue(getQueryParamDatas(), $var, $defaultValue);
	}

	function getOptionalBodyValue($var, $defaultValue) {
		return getOptionalValue(getBodyParamDatas(), $var, $defaultValue);
	}

	function getOptionalValue($datas, $var, $defaultValue) {
		foreach($datas as $data) {
			if(isset($data[$var]) && $data[$var] !== null) {
				return $data[$var];
			}
		}
		return $defaultValue;
	}

	function getAllBodyValues() {
		$datas = getBodyParamDatas();

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

	function getQueryParamDatas() {
		return array($_GET);
	}

	function getBodyParamDatas() {
		$datas = array();

		if(isset($_SERVER["CONTENT_TYPE"]) && $_SERVER["CONTENT_TYPE"] == "application/json") {
			array_push($datas, jsonDecode(file_get_contents('php://input')));
		}
		
		// application/x-www-form-urlencoded
		array_push($datas, $_POST);

		if($_SERVER['REQUEST_METHOD'] == Method::PUT) {
			parse_str(file_get_contents("php://input"), $data);
			array_push($datas, $data);
		}
		
		return $datas;
	}

	function getRequestDatas() {
		$datas = array();

		if(isset($_SERVER["CONTENT_TYPE"]) && $_SERVER["CONTENT_TYPE"] == "application/json") {
			array_push($datas, jsonDecode(file_get_contents('php://input')));
		}
		
		// application/x-www-form-urlencoded
		$method = $_SERVER['REQUEST_METHOD'];
		if($method == Method::GET) {
			//array_push($datas, $_GET);
		} else if($method == Method::POST) {
			//array_push($datas, $_POST);
		} else if($method == Method::PUT) {
			parse_str(file_get_contents("php://input"), $data);
			array_push($datas, $data);
		} else {
			//array_push($datas, $_REQUEST);
		}

		array_push($datas, $_REQUEST);
		return $datas;
	}

