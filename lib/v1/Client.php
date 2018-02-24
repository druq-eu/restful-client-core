<?php

namespace druq\restful\client\core\v1;

use druq\restful\client\core\response\Auth;
use druq\restful\client\core\ResponseFactory;
use druq\restful\client\core\RestfulClient;

class Client extends RestfulClient {

    public function login() {
        $url = $this->getBaseUrl() . 'login?email=' . urlencode($this->login) . '&pwd=' . urlencode($this->pass);
        $json = json_decode(file_get_contents($url));
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
        $response = $this->request($url);
        return ResponseFactory::createDataObjectList($class, $response);
    }

}