<?php

registerEndpoint(Method::POST, Authorization::NONE, "session/login/{username}", function($username) {
    $password = getMandatoryRequestValue("password");
    if(login($username, $password)) {
        return "Successfully logged in as $username";
    }
    requestAuthFail("Invalid credentials");
});

registerEndpoint(Method::POST, Authorization::USER, "session/logout", function() {
    logout();
    return "Successfully logged out";
});
