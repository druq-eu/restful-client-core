<?php

namespace druq\restful\client\core;

class ResponseFactory {

    /**
     * @param string $class
     * @param \stdClass|array $json
     * @return int
     * @throws Exception
     */
    public static function create($class, $json) {
        if (is_array($json)) return self::constructFromArray($class, $json);
        if ($json instanceof \stdClass) return self::constructFromObject($class, $json);
        return null;
    }

    /**
     * @param string $class
     * @param array|\stdClass $json
     * @return static
     */
    public static function createDataObjectList($class, $collectionJson) {
        $dol = DataObjectList::create((array) $collectionJson);
        $dol->setModel($class);
        $items = [];
        foreach ($dol->getItems() as $item) {
            $currClass = self::getClassName($class, $item);
            $do = $currClass::create($item);
            if (property_exists($do, 'ID') && $do->ID > 0) $items[$do->ID] = $do;
            else $items[] = $do;
        }
        $dol->setItems($items);
        return $dol;
    }

    /**
     * @param string $class
     * @param array $array
     * @return array
     */
    protected static function constructFromArray($class, $array) {
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
    protected static function getClassName($baseClass, &$array) {
        $class = $baseClass;
        $array = (array) $array;
        if (isset($array['ObjectType'])) {
            $namespace = preg_replace('/\\\S+$/', '\\', $baseClass);
            $candidate = $namespace.$array['ObjectType'];
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
    protected static function constructFromObject($class, $obj) {
        /** @var DataObject $class */
        return $class::create($obj);
    }

}
