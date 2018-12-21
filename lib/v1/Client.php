<?php

namespace druq\restful\client\core\v1;

use druq\restful\client\core\Filter;
use druq\restful\client\core\response\Auth;
use druq\restful\client\core\ResponseFactory;
use druq\restful\client\core\RestfulClient;

class Client extends RestfulClient
{

    public $locale;

    /**
     * @return bool
     */
    public function login()
    {
        $url = $this->getBaseUrl() . 'login?email=' . urlencode($this->login) . '&pwd=' . urlencode($this->pass);
        $json = json_decode(file_get_contents($url), true);
        $auth = Auth::create($json);
        if ($auth->result && $auth->token) {
            $this->token = $auth->token;
            $this->expire = $auth->expire;
            return true;
        }
        return false;
    }

    /**
     * @param string $url
     * @param array $params
     * @param string $method
     * @return array|\stdClass
     * @throws \Exception
     */
    protected function request($url, $params = array(), $method = 'GET')
    {
        if ($this->locale && !isset($params['_locale'])) {
            $params['_locale'] = $this->locale;
        }
        return parent::request($url, $params, $method);
    }

    /**
     * @param string $class
     * @param array|int $ids
     * @return \druq\restful\client\core\DataObjectList
     * @throws \Exception
     */
    public function getByIds($class, $ids)
    {
        $ids = \is_array($ids) ? $ids : [$ids];
        $url = $this->getBaseUrl() . self::sanitizeClassName($class) . '/,' . implode(',',
                array_filter($ids, 'is_int'));
        $response = $this->request($url, ['_view_collection' => 1]);
        return ResponseFactory::createDataObjectList($class, $response);
    }

    /**
     * @param string $class
     * @return \druq\restful\client\core\DataObjectList
     * @throws \Exception
     */
    public function getAll($class)
    {
        return $this->getByFilter($class, Filter::create());
    }

    /**
     * @param string $class
     * @param Filter $filter
     * @return \druq\restful\client\core\DataObjectList
     * @throws \Exception
     */
    public function getByFilter($class, Filter $filter)
    {
        $url = $this->getBaseUrl() . self::sanitizeClassName($class);
        $params = $filter->getParams();
        $params['_view_collection'] = true;
        $response = $this->request($url, $params);
        return ResponseFactory::createDataObjectList($class, $response);
    }

    /**
     * @param string $class
     * @param array $record
     * @return \druq\restful\client\core\DataObject
     * @throws \Exception
     */
    public function createRecord($class, $record)
    {
        $url = $this->getBaseUrl() . self::sanitizeClassName($class);
        $response = $this->request($url, $record, 'POST');
        return ResponseFactory::createDataObject($class, $response);
    }

    /**
     * @param string $class
     * @param array $records
     * @return \druq\restful\client\core\DataObjectList
     * @throws \Exception
     */
    public function createRecords($class, $records)
    {
        $url = $this->getBaseUrl() . self::sanitizeClassName($class);
        $responseArray = $this->request($url, $records, 'POST');
        if (isset($responseArray->totalCount, $responseArray->items, $responseArray->itemsCount)) {
            return ResponseFactory::createDataObjectList($class, $responseArray);
        }
        $count = \count($responseArray);
        return ResponseFactory::createDataObjectList($class, [
            'totalCount' => $count,
            'itemsCount' => $count,
            'perPage' => $count,
            'pageNo' => 1,
            'items' => $responseArray,
        ]);
    }

}
