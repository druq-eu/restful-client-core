<?php

namespace druq\restful\client\core;

class Filter
{

    /** @var array */
    private $params;

    /**
     * Filter constructor.
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        $this->params = $params;
    }

    public static function create(array $params = [])
    {
        return new static($params);
    }

    public function getAsUrlParams()
    {
        return http_build_query($this->params);
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

}
