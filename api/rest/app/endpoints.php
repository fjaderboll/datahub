<?php

$endpoints = array();

function registerEndpoint($method, $url, $func) {
    global $endpoints;

    if(!isset($endpoints[$method])) {
        $endpoints[$method] = array();
    }
    $endpoints[$method][$url] = $func;
}

function verifyEndpointExists() {
    findRequestHandler(false);
}

function executeEndpointFunction() {
    return findRequestHandler(true);
}

function findRequestHandler($execute) {
    global $endpoints;

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
