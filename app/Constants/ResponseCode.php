<?php

namespace App\Constants;

class ResponseCode
{
    // Success
    const SUCCESS = 200;
    const CREATED = 201;

    // Client Errors
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const METHOD_NOT_ALLOWED = 405;
    const UNPROCESSABLE_ENTITY = 422;

    // Server Errors
    const INTERNAL_SERVER_ERROR = 500;

    // Messages
    const MSG_SUCCESS = 'Operation successful.';
    const MSG_CREATED = 'Resource created successfully.';
    const MSG_BAD_REQUEST = 'Bad request.';
    const MSG_UNAUTHORIZED = 'Unauthorized access.';
    const MSG_FORBIDDEN = 'Access forbidden.';
    const MSG_NOT_FOUND = 'Resource not found.';
    const MSG_INTERNAL_ERROR = 'Something went wrong. Please try again later.';
}
