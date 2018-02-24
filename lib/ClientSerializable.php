<?php

namespace druq\restful\client\core;

/**
 * Interface ClientSerializable
 * @package druq\restful\client\core
 * Interface to implement to store restful client objects 
 */
interface ClientSerializable {

    /**
     * @param RestfulClient[] $restfulClients
     * @return bool
     */
    public function saveClients($restfulClients);

    /**
     * @return RestfulClient[]
     */
    public function loadClients();

}