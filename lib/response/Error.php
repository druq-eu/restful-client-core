<?php

namespace druq\restful\client\core\response;

use druq\restful\client\core\Object;

/**
 * Class Error
 * @package druq\restful\client\core\response
 */
class Error extends Object
{

    const ERROR_CODE_CLASS_NOT_FOUND = 101;
    const ERROR_CODE_WRONG_HTTPMETHOD = 102;
    const ERROR_CODE_COULDNOT_CREATE_OBJECT = 201;
    const ERROR_CODE_COULDNOT_CREATE_OBJECTS = 202;
    const ERROR_CODE_COULDNOT_FIND_OBJECT = 301;
    const ERROR_CODE_COULDNOT_FIND_OBJECTS = 302;
    const ERROR_CODE_COULDNOT_DELETE_OBJECT = 401;
    const ERROR_CODE_COULDNOT_DELETE_OBJECTS = 402;
    const ERROR_CODE_COULDNOT_DELETE_ALL_OBJECTS = 403;

    /** @var true */
    public $error;

    /** @var int */
    public $code;

    /** @var string */
    public $message;

}