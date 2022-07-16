<?php

/**
 * @OA\Post(
 *     path="/exports",
 *     summary="Create new export configuration",
 *     @OA\RequestBody(
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 @OA\Property(property="enabled", type="boolean"),
 *                 @OA\Property(property="protocol", type="string"),
 *                 @OA\Property(property="format", type="string"),
 *                 @OA\Property(property="url", type="string"),
 *                 @OA\Property(property="auth1", type="string"),
 *                 @OA\Property(property="auth2", type="string"),
 *                 example={"enabled": true, "protocol": "HTTP", "format": "JSON", "url": "https://iot.mydomain.com/data", "auth1": "Authorization", "auth2": "abcdef123456"}
 *             )
 *         )
 *     ),
 *     @OA\Response(response=200, description="OK")
 * )
 */
registerEndpoint(Method::POST, Authorization::USER, Operation::WRITE, "exports", function() {
    $enabled = getOptionalBodyValue("enabled", 1);
    $protocol = strtoupper(getMandatoryBodyValue("protocol"));
    $format = strtoupper(getMandatoryBodyValue("format"));
    $url = getMandatoryBodyValue("url");
    $auth1 = getOptionalBodyValue("auth1", null);
    $auth2 = getOptionalBodyValue("auth2", null);

    if(getExportProtocol($protocol) === false) {
        requestParameterFail("Unknown protocol: $protocol");
    }
    if(getExportFormat($format) === false) {
        requestParameterFail("Unknown format: $format");
    }

    dbUpdate("INSERT INTO export(enabled, protocol, format, url, auth1, auth2) VALUES (?, ?, ?, ?, ?, ?)", toDbBoolean($enabled), $protocol, $format, $url, $auth1, $auth2);

    return "Export created";
});

/**
 * @OA\Get(
 *     path="/exports",
 *     summary="List all exports",
 *     @OA\Response(response=200, description="OK")
 * )
 */
registerEndpoint(Method::GET, Authorization::USER, Operation::READ, "exports", function() {
    return getExports();
});

/**
 * @OA\Get(
 *     path="/exports/protocols",
 *     summary="List possible export protocols",
 *     @OA\Response(response=200, description="OK")
 * )
 */
registerEndpoint(Method::GET, Authorization::USER, Operation::READ, "exports/protocols", function() {
    return getExportProtocols();
});

/**
 * @OA\Get(
 *     path="/exports/formats",
 *     summary="List possible export formats",
 *     @OA\Response(response=200, description="OK")
 * )
 */
registerEndpoint(Method::GET, Authorization::USER, Operation::READ, "exports/formats", function() {
    return getExportFormats();
});

/**
 * @OA\Put(
 *     path="/exports/{id}",
 *     summary="Update export",
 *     @OA\Parameter(
 *         description="Id of export.",
 *         in="path",
 *         name="id",
 *         required=true,
 *         @OA\Schema(type="number")
 *     ),
 *     @OA\RequestBody(
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 @OA\Property(property="email", type="string"),
 *                 example={"enabled": false, "format": "JSON"}
 *             )
 *         )
 *     ),
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=404, description="Export not found")
 * )
 */
registerEndpoint(Method::PUT, Authorization::USER, Operation::WRITE, "exports/{id}", function($id) {
    $dbExport = findExport($id);

    $changes = 0;
    $clear = false;

    $enabled = getOptionalBodyValue("enabled", null);
    if($enabled !== null) {
        $changes += dbUpdate("UPDATE export SET enabled = ? WHERE id = ?", toDbBoolean($enabled), $id);
        $clear = $enabled;
    }

    $protocol = getOptionalBodyValue("protocol", null);
    if($protocol !== null) {
        if(getExportProtocol($protocol) === false) {
            requestParameterFail("Unknown protocol: $protocol");
        }
        $changes += dbUpdate("UPDATE export SET protocol = ? WHERE id = ?", $protocol, $id);
        $clear = true;
    }

    $format = getOptionalBodyValue("format", null);
    if($format !== null) {
        if(getExportFormat($format) === false) {
            requestParameterFail("Unknown format: $format");
        }
        $changes += dbUpdate("UPDATE export SET format = ? WHERE id = ?", $format, $id);
    }

    $url = getOptionalBodyValue("url", null);
    if($url !== null) {
        $changes += dbUpdate("UPDATE export SET url = ? WHERE id = ?", $url, $id);
        $clear = true;
    }

    $auth1 = getOptionalBodyValue("auth1", null);
    if($auth1 !== null) {
        $changes += dbUpdate("UPDATE export SET auth1 = ? WHERE id = ?", $auth1, $id);
        $clear = true;
    }

    $auth2 = getOptionalBodyValue("auth2", null);
    if($auth2 !== null) {
        $changes += dbUpdate("UPDATE export SET auth2 = ? WHERE id = ?", $auth2, $id);
        $clear = true;
    }

    if($clear) {
        $changes += dbUpdate("UPDATE export SET fail_count = ?, status = ? WHERE id = ?", 0, "Ready", $id);
    }

    return ($changes > 0 ? "Export updated" : "Nothing updated");
});

/**
 * @OA\Delete(
 *     path="/exports/{id}",
 *     summary="Delete export",
 *     @OA\Parameter(
 *         description="Id of export.",
 *         in="path",
 *         name="id",
 *         required=true,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(response=200, description="OK"),
 *     @OA\Response(response=404, description="Export not found")
 * )
 */
registerEndpoint(Method::DELETE, Authorization::USER, Operation::WRITE, "exports/{id}", function($id) {
    $dbExport = findExport($id);

    dbUpdate("DELETE FROM export WHERE id = ?", $id);

    return "Export deleted";
});

// ----------------------
function findExport($exportId) {
    $dbExports = dbQuery("SELECT * FROM export WHERE id = ?", $exportId);
    if(count($dbExports) == 0) {
        requestFail("Export not found", 404);
    } else {
        return $dbExports[0];
    }
}

function getExports() {
    $dbExports = dbQuery("SELECT * FROM export");
    $exports = array();
    foreach($dbExports as $dbExport) {
		$export = convertFromDbObject($dbExport, array('id', 'enabled', 'protocol', 'format', 'url', 'auth1', 'auth2', 'fail_count', 'status'));
        $export['enabled'] = toBoolean($export['enabled']);
        $export['protocol'] = getExportProtocol($export['protocol']);
        $export['format'] = getExportFormat($export['format']);
        
        array_push($exports, $export);
	}
    return $exports;
}

function getExportProtocol($code) {
    foreach(getExportProtocols() as $p) {
        if($p['code'] == $code) {
            return $p;
        }
    }
    return false;
}

function getExportFormat($code) {
    foreach(getExportFormats() as $f) {
        if($f['code'] == $code) {
            return $f;
        }
    }
    return false;
}

function getExportProtocols() {
    return array(
        array(
            'name' => 'HTTP',
            'code' => 'HTTP',
            'auth1Name' => 'Header key',
            'auth2Name' => 'Header value',
        ),
        array(
            'name' => 'MQTT',
            'code' => 'MQTT',
            'auth1Name' => 'Username',
            'auth2Name' => 'Password',
        )
    );
}

function getExportFormats() {
    return array(
        array(
            'name' => 'JSON',
            'code' => 'JSON'
        ),
        array(
            'name' => 'CSV',
            'code' => 'CSV'
        )
    );
}

function performExports($readingIds) {
    if(count($readingIds) == 0) {
        return;
    }
    
    $dbExports = dbQuery("SELECT * FROM export WHERE enabled = ?", toDbBoolean(true));
    if(count($dbExports) == 0) {
        return;
    }

    $readings = array();
    foreach($readingIds as $readingId) {
        $reading = getReading($readingId);
        if($reading) {
            array_push($readings, $reading);
        }
    }
    if(count($readings) == 0) {
        return;
    }

	foreach($dbExports as $dbExport) {
        $data = null;
        if($dbExport['format'] == 'JSON') {
            $contentType = "application/json";
            $data = jsonEncode($readings);
        } else if($dbExport['format'] == 'CSV') {
            $contentType = "text/csv";
            $keys = array_keys($readings[0]);
            $data = implode(",", $keys)."\n";
            foreach($readings as $reading) {
                $values = array();
                foreach($keys as $key) {
                    array_push($values, $reading[$key]);
                }
                $data .= implode(",", $values)."\n";
            }
        }

		if($data) {
            if($dbExport['protocol'] == 'HTTP') {
                $options = array(
                    'http' => array(
                        'header'  => "Content-type: $contentType\r\n",
                        'method'  => 'POST',
                        'content' => $data,
                        'ignore_errors' => true, // suppress warnings written to response
						'timeout' => 1
                    )
                );
                if($dbExport['auth1'] && $dbExport['auth1'] != "") {
                    $options['http']['header'] .= $dbExport['auth1'].": ".$dbExport['auth2']."\r\n";
                }

                $context = stream_context_create($options);
                $result = file_get_contents($dbExport['url'], false, $context);
                if($result === FALSE) {
                    dbUpdate("UPDATE export SET fail_count = ?, status = ?, enabled = ? WHERE id = ?", $dbExport['fail_count'] + 1, "Error", toDbBoolean($dbExport['fail_count'] < 99), $dbExport['id']);
                } else {
					$statusLine = $http_response_header[0];
					preg_match('{HTTP\/\S*\s(\d{3})}', $statusLine, $match);
					$statusCode = $match[1];

					if(200 <= $statusCode && $statusCode < 300) {
                    	dbUpdate("UPDATE export SET fail_count = 0, status = ? WHERE id = ? AND (fail_count != 0 OR status != ?)", $statusLine, $dbExport['id'], $statusLine);
					} else {
						dbUpdate("UPDATE export SET fail_count = ?, status = ?, enabled = ? WHERE id = ?", $dbExport['fail_count'] + 1, $statusLine, toDbBoolean($dbExport['fail_count'] < 99), $dbExport['id']);
					}
                }
            } else if($dbExport['protocol'] == 'MQTT') {
                dbUpdate("UPDATE export SET fail_count = ?, status = ?, enabled = ? WHERE id = ?", $dbExport['fail_count'] + 1, "Not implemented", toDbBoolean(false), $dbExport['id']);
            }
        }
    }
}
