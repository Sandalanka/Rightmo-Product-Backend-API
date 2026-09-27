<?php

namespace App\Constants;

class StatusCodeConstant
{
    // Success status codes
    const OK = 200;

    const CREATED = 201;

    // Client error status codes
    const UNAUTHORIZED = 401;

    const UNPROCESSABLE_ENTITY = 422;

    // Server error status codes
    const INTERNAL_SERVER_ERROR = 500;
}
