<?php

abstract class Method {
    const GET    = "GET";
    const POST   = "POST";
    const PUT    = "PUT";
    const DELETE = "DELETE";
}

abstract class Authorization {
    const NONE   = "NONE";   // accessible to all
    const DEVICE = "DEVICE"; // requires device or user token
    const USER   = "USER";   // requires user token
    const ADMIN  = "ADMIN";  // requires user token + 'admin'
}

abstract class Operation {
    const READ  = "READ";    // no changes will be made in request
    const WRITE = "WRITE";   // at least one change will be written
}
