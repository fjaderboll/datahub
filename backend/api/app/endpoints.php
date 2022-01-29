<?php

$endpoints = array();
$endpointAuth = array();
$endpointOperation = array();

function registerEndpoint($method, $auth, $operation, $url, $func) {
    global $endpoints, $endpointAuth, $endpointOperation;

    if(!isset($endpoints[$method])) {
        $endpoints[$method] = array();
        $endpointAuth[$method] = array();
        $endpointOperation[$method] = array();
    }
    $endpoints[$method][$url] = $func;
    $endpointAuth[$method][$url] = $auth;
    $endpointOperation[$method][$url] = $operation;
}

function verifyEndpoint() {
    findRequestHandler(false);
}

function executeEndpoint() {
    return findRequestHandler(true);
}

function findRequestHandler($execute) {
    global $endpoints, $endpointAuth, $endpointOperation;

    $url = $_SERVER['REDIRECT_URL'];
    $url = substr($url, strpos($url, 'api/') + 4);
    $url = rtrim($url, '/');
    $urlParts = explode('/', $url);
    $un = count($urlParts);
    $method = $_SERVER['REQUEST_METHOD'];

    if(isset($endpoints[$method])) {
        foreach($endpoints[$method] as $eUrl => $eFunc) {
            $eUrlParts = explode('/', $eUrl);
            $en = count($eUrlParts);

            if($un == $en) {
                $match = true;
                $params = array();
                for($i = 0; $i < $un; $i++) {
                    if($urlParts[$i] == $eUrlParts[$i]) {
                        continue;
                    } else if(preg_match('/\{[a-z]+\}/i', $eUrlParts[$i])) {
                        array_push($params, $urlParts[$i]);
                    } else {
                        $match = false;
                        break;
                    }
                }

                if($match) {
                    $auth = $endpointAuth[$method][$eUrl];
                    verifyAuthorized($auth);

                    $operation = $endpointOperation[$method][$eUrl];
                    verifyOperation($operation);

                    if($execute) {
                        return $eFunc(...$params);
                    } else {
                        return true;
                    }
                }
            }
        }
    }
    requestFail("Unknown request: $method $url", 404);
}

function verifyAuthorized($endpointAuth) {
    $ok = false;
    $tokenAuth = getTokenAuth();

    if($endpointAuth == Authorization::NONE) {
        $ok = true;
    } else if($endpointAuth == Authorization::DEVICE) {
        $ok = $tokenAuth != Authorization::NONE;
    } else {
        $ok = $endpointAuth == $tokenAuth || $tokenAuth == Authorization::ADMIN;
    }

    if(!$ok) {
        requestAuthFail("Not authorized");
    }
}

function verifyOperation($operation) {
    $ok = false;

    if($operation == Operation::READ) {
        $ok = getRead();
    } else if($operation == Operation::WRITE) {
        $ok = getWrite();
    }

    if(!$ok) {
        requestAuthFail("Not authorized");
    }
}