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
		//$sources = array($_GET, $_POST);
		$sources = array($_REQUEST);

		foreach($sources as $data) {
			if(isset($data[$var]) && $data[$var] != null && $data[$var] != "") {
				return $data[$var];
			}
		}
		return $defaultValue;
	}

	function verifyDatasetModify() {
		$dbDataset = dbQuerySingle("SELECT permission_admin FROM e_dataset WHERE id = ? AND user_id = ?", getDatasetId(), getUserId());
		if($dbDataset['permission_admin'] == 0) {
			requestAuthFail("Unauthorized action");
		}
	}
