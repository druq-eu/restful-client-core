<?php

namespace druq\restful\client\core\storage;

use druq\restful\client\core\ClientSerializable;

class RequestStorage implements ClientSerializable
{

    private static $clients = array();

    public function saveClients($restfulClients)
    {
        self::$clients = $restfulClients;
    }

    public function loadClients()
    {
        return self::$clients ?: null;
    }

}