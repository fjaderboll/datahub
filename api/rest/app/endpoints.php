<?php

$endpoints = array();
$endpointAuth = array();

function registerEndpoint($method, $auth, $url, $func) {
    global $endpoints, $endpointAuth;

    if(!isset($endpoints[$method])) {
        $endpoints[$method] = array();
        $endpointAuth[$method] = array();
    }
    $endpoints[$method][$url] = $func;
    $endpointAuth[$method][$url] = $auth;
}

function verifyEndpoint() {
    findRequestHandler(false);
}

function executeEndpoint() {
    return findRequestHandler(true);
}

function findRequestHandler($execute) {
    global $endpoints, $endpointAuth;

    $url = $_SERVER['REDIRECT_URL'];
    $url = substr($url, strpos($url, '/rest/') + 6);
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
