<?php

abstract class Method {
    const GET    = "GET";
    const POST   = "POST";
    const PUT    = "PUT";
    const DELETE = "DELETE";
}

abstract class Authorization {
    const NONE    = "NONE";    // accessible to all
    const DATASET = "DATASET"; // requires dataset or user token
    const USER    = "USER";    // requires user token
    const ADMIN   = "ADMIN";   // requires user token + 'admin'
}
