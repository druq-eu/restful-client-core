<?php

namespace druq\restful\client\core\v1;

use druq\restful\client\core\response\Auth;
use druq\restful\client\core\ResponseFactory;
use druq\restful\client\core\RestfulClient;
use druq\restful\client\core\Filter;

class Client extends RestfulClient {

    public function login() {
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

    public function getByIds($class, $ids) {
        $ids = is_array($ids) ? $ids : array($ids);
        $url = $this->getBaseUrl().self::sanitizeClassName($class).'/,'.implode(',', array_filter($ids, 'is_int'));
        $response = $this->request($url, ['_view_collection' => 1]);
        return ResponseFactory::createDataObjectList($class, $response);
    }

    public function getAll($class)
    {
        return static::getByFilter($class, Filter::create());
    }

    public function getByFilter($class, Filter $filter)
    {
        $url = $this->getBaseUrl() . self::sanitizeClassName($class);
        $params = $filter->getParams();
        $params['_view_collection'] = true;
        $response = $this->request($url, $params);
        return ResponseFactory::createDataObjectList($class, $response);
    }

}
