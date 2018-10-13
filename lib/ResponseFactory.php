<?php

namespace druq\restful\client\core;

class ResponseFactory
{

    /**
     * @param string $class
     * @param \stdClass|array $json
     * @return DataObject|DataObject[]
     */
    public static function create($class, $json)
    {
        if (is_array($json)) {
            return self::constructFromArray($class, $json);
        }
        if ($json instanceof \stdClass) {
            return self::constructFromObject($class, $json);
        }
        return null;
    }

    /**
     * @param string $class
     * @param array $collectionJson
     * @return DataObjectList
     */
    public static function createDataObjectList($class, $collectionJson)
    {
        $dol = DataObjectList::create((array)$collectionJson);
        $dol->setModel($class);
        $items = [];
        foreach ($dol->getItems() as $item) {
            $currClass = self::getClassName($class, $item);
            /** @var DataObject $do */
            $do = $currClass::create($item);
            if ($do->ID > 0 && property_exists($do, 'ID')) {
                $items[$do->ID] = $do;
            } else {
                $items[] = $do;
            }
        }
        $dol->setItems($items);
        return $dol;
    }

    public static function createDataObject($class, $json)
    {
        return self::constructFromObject($class, $json);
    }

    /**
     * @param string $class
     * @param array $array
     * @return array
     */
    protected static function constructFromArray($class, $array)
    {
        $arr = array();
        foreach ($array as $json) {
            $currClass = self::getClassName($class, $json);
            $arr[] = $currClass::create($json);
        }
        return $arr;
    }

    /**
     * @param string $baseClass
     * @param array|object $array
     * @return string|DataObject
     */
    protected static function getClassName($baseClass, &$array)
    {
        $class = $baseClass;
        $array = (array)$array;
        if (isset($array['ObjectType'])) {
            $namespace = preg_replace('/\\\S+$/', '\\', $baseClass);
            $candidate = $namespace . $array['ObjectType'];
            if (class_exists($candidate) && ($baseClass == $candidate || is_subclass_of($candidate, $baseClass))) {
                $class = $candidate;
            }
        }
        return $class;
    }

    /**
     * @param string $class
     * @param \stdClass $obj
     * @return DataObject
     */
    protected static function constructFromObject($class, $obj)
    {
        /** @var DataObject $class */
        return $class::create($obj);
    }

}
