<?php
namespace App\Interfaces;

interface StatusCode {

    const OK = 200;
    const CREATED = 201;
    const UPDATED = 202;
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const VALIDATION = 422;
    const SERVER_ERROR = 500;
}
