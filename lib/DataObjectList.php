<?php

namespace druq\restful\client\core;

class DataObjectList extends Object
{

    /** @var string */
    private $model;

    /** @var int */
    private $totalCount;

    /** @var int */
    private $itemsCount;

    /** @var int */
    private $perPage;

    /** @var int */
    private $pageNo;

    /** @var DataObject[] */
    private $items = [];

    /**
     * @return int
     */
    public function getTotalCount()
    {
        return $this->totalCount;
    }

    /**
     * @param int $totalCount
     * @return static
     */
    public function setTotalCount($totalCount)
    {
        $this->totalCount = $totalCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getItemsCount()
    {
        return $this->itemsCount;
    }

    /**
     * @param int $itemsCount
     * @return static
     */
    public function setItemsCount($itemsCount)
    {
        $this->itemsCount = $itemsCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getPerPage()
    {
        return $this->perPage;
    }

    /**
     * @param int $perPage
     * @return static
     */
    public function setPerPage($perPage)
    {
        $this->perPage = $perPage;
        return $this;
    }

    /**
     * @return int
     */
    public function getPageNo()
    {
        return $this->pageNo;
    }

    /**
     * @param int $pageNo
     * @return static
     */
    public function setPageNo($pageNo)
    {
        $this->pageNo = $pageNo;
        return $this;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param array $items
     * @return static
     */
    public function setItems($items)
    {
        $this->items = $items;
        return $this;
    }

    /**
     * @return DataObject|null
     */
    public function first()
    {
        return reset($this->items);
    }

    /**
     * @return DataObject|null
     */
    public function last()
    {
        return end($this->items);
    }

    /**
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param string $model
     * @return static
     */
    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }

    /**
     * @param string $keyField
     * @param string $valueField
     * @return array
     */
    public function map($keyField = 'ID', $valueField = 'Title')
    {
        $arr = [];
        foreach ($this->items as $item) {
            $arr[$item->{$keyField}] = $item->{$valueField};
        }
        return $arr;
    }

}
