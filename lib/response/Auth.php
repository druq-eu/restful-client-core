<?php

namespace druq\restful\client\core\response;

use druq\restful\client\core\Object;

class Auth extends Object {

    const AUTH_CODE_LOGGED_IN = 0;
    const AUTH_CODE_LOGIN_FAIL = 1;
    const AUTH_CODE_TOKEN_INVALID = 2;
    const AUTH_CODE_TOKEN_EXPIRED = 3;

    /** @var bool */
    public $result;
    /** @var int */
    public $code;
    /** @var string */
    public $message;
    /** @var int */
    public $expire;
    /** @var string */
    public $token;

    public function isTokenExpired() {
        return $this->code == self::AUTH_CODE_TOKEN_EXPIRED;
    }

    public function isTokenInvalid() {
        return $this->code == self::AUTH_CODE_TOKEN_INVALID;
    }

}