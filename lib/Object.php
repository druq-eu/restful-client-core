<?php

namespace druq\restful\client\core;

abstract class Object
{

    /**
     * @return string
     */
    public static function className()
    {
        return get_called_class();
    }

    /**
     * @param array $array
     * @return static
     */
    public static function create($array = [])
    {
        $class = get_called_class();
        $obj = new $class();
        if (is_array($array)) {
            foreach ($array as $name => $value) {
                $setter = 'set' . ucfirst($name);
                if (method_exists($obj, $setter)) {
                    $obj->{$setter}($value);
                } else {
                    $obj->{$name} = $value;
                }
            }
        }
        return $obj;
    }

}
