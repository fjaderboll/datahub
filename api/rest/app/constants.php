<?php

abstract class Method {
    const GET = "GET";
    const POST = "POST";
    const PUT = "PUT";
    const DELETE = "DELETE";
}

abstract class Authorization {
    const NONE = "NONE";
    const USER = "USER";
    const ADMIN = "ADMIN";
}
