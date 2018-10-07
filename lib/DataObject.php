<?php

namespace druq\restful\client\core;

/**
 * A single database record & abstract class for the data-access-model.
 */
class DataObject extends Object {

	/**
	 * Data stored in this objects database record. An array indexed by fieldname.
	 * Use {@link toMap()} if you want an array representation of this object,
	 * as the $record array might contain lazy loaded field aliases.
	 *
	 * @var array
	 */
	protected $record;

	/**
	 * The database record (in the same format as $record), before any changes.
	 * @var array
	 */
	protected $original;

	/**
	 * Construct a new DataObject.
	 * @param array|null $record This will be null for a new database record.
	 * Alternatively, you can pass an array of field values.
	 * Normally this contructor is only used by the internal systems that get objects from the restful server.
	 */
	public function __construct($record = null) {
		$this->record = (array) $record;
		$this->original = (array) $record;
	}

	/**
	 * since loading them from the restful server.
	 * @param string $fieldName
	 * @return bool
	 */
	public function isChanged($fieldName = null) {
		if ($fieldName) {
			return !(isset($this->record[$fieldName]) && $this->record[$fieldName] != $this->original[$fieldName]);
		}
		return (bool) array_diff_assoc($this->record, $this->original);
	}

	/**
	 * @param int $id
	 * @param RestfulClient $restfulClient
	 * @return DataObject|null
	 */
	public static function getByID($id, $restfulClient = null) {
		return static::getByIDs(array($id), $restfulClient)->first();
	}

	/**
	 * @param array $ids
	 * @param RestfulClient $restfulClient
	 * @return DataObjectList
	 */
	public static function getByIDs($ids, $restfulClient = null) {
        $restfulClient = $restfulClient ?: RestfulClient::getLastClient();
		$class = get_called_class();
		return $restfulClient->getByIds($class, $ids);
	}

    /**
     * @param Filter $filter
     * @param RestfulClient $restfulClient
     * @return DataObjectList
     */
    public static function getAll($restfulClient = null)
    {
        $restfulClient = $restfulClient ?: RestfulClient::getLastClient();
        $class = get_called_class();
        return $restfulClient->getAll($class);
	}

    /**
     * @param Filter $filter
     * @param RestfulClient $restfulClient
     * @return DataObjectList
     */
    public static function getByFilter(Filter $filter, $restfulClient = null)
    {
        $restfulClient = $restfulClient ?: RestfulClient::getLastClient();
        $class = get_called_class();
        return $restfulClient->getByFilter($class, $filter);
	}

	/**
	 * @param array $array
	 * @return static
	 */
	public static function create($array = []) {
		return new static($array);
	}

	/**
	 * @param $name
	 * @return mixed|null
	 */
	function __get($name) {
		return isset($this->record[$name]) ? $this->record[$name] : null;
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 */
	function __set($name, $value) {
		if (isset($this->record[$name])) $this->record[$name] = $value;
	}

}
