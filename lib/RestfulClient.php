<?php

namespace druq\restful\client\core;

use druq\restful\client\core\response\Auth;
use druq\restful\client\core\response\Error;
use druq\restful\client\core\storage\RequestStorage;

abstract class RestfulClient
{

    /** @var RestfulClient[] */
    private static $clients;
    /** @var RestfulClient */
    private static $last_client;
    /** @var ClientSerializable */
    private static $storage;
    /** @var string */
    protected $url;
    /** @var string */
    protected $login;
    /** @var string */
    protected $pass;
    /** @var string */
    protected $expire;
    /** @var string */
    protected $token;
    /** @var string */
    private $version;

    /**
     * @param ClientSerializable $storage
     * Register storage for RestfulClient objects
     * Be careful about password!
     * Request storage used if not provided any
     */
    public static function registerClientStorage(ClientSerializable $storage)
    {
        self::$storage = $storage;
    }

    /**
     * @return ClientSerializable
     */
    private static function getStorage()
    {
        return self::$storage ?: self::$storage = new RequestStorage();
    }

    /**
     * @param bool $forceLoad
     */
    private static function loadClients($forceLoad = false)
    {
        if (self::$clients && !$forceLoad) {
            return;
        }
        $clients = self::getStorage()->loadClients();
        self::$clients = array();
        if (is_array($clients)) {
            foreach ($clients as $client) {
                self::$clients[$client->getKey()] = $client;
            }
        }
        if (self::$clients && !self::$last_client) {
            self::$last_client = end(self::$clients);
        }
    }

    /**
     * @return bool
     */
    private static function saveClients()
    {
        return self::getStorage()->saveClients(self::$clients);
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return self::getKeyHash($this->getBaseUrl(), $this->login);
    }

    /**
     * @param string $baseUrl
     * @param string $login
     * @return string
     */
    private static function getKeyHash($baseUrl, $login)
    {
        return md5(md5($baseUrl) . md5($login));
    }

    /**
     * @param string $url
     * @param string $login
     * @param string $pass
     * @param string $version
     * @return RestfulClient
     */
    public static function registerClient($url, $login, $pass, $version = '1')
    {
        return self::getClient($url, $login, $pass, $version);
    }

    /**
     * @param string $url
     * @param string $login
     * @param string $pass
     * @param string $version
     * @return RestfulClient
     */
    public static function getClient($url, $login, $pass, $version = '1')
    {
        if (!self::$clients) {
            self::loadClients();
        }
        $key = self::getKeyHash(static::constructBaseUrl($url, $version), $login);
        $write = false;
        if (!isset(self::$clients[$key])) {
            $class = static::getClientClass($version);
            /** @var RestfulClient $client */
            self::$clients[$key] = new $class();
            self::$clients[$key]->setUrl($url);
            self::$clients[$key]->setLogin($login);
            self::$clients[$key]->setPass($pass);
            self::$clients[$key]->setVersion($version);
            $write = true;
        }
        self::$last_client = self::$clients[$key];
        if (self::$clients[$key]->pass != $pass) {
            self::$clients[$key]->pass = $pass;
            $write = true;
        }
        if (self::$clients[$key]->isExpired()) {
            $write = (bool)self::$clients[$key]->login();
        }
        if ($write) {
            self::saveClients();
        }

//        \Debug::dump(self::$clients);

        return self::$clients[$key];
    }

    public static function getClientClass($version)
    {
        return __NAMESPACE__ . '\\v' . $version . '\\Client';
    }

    /**
     * @return bool
     */
    public function isExpired()
    {
        return $this->expire && time() > $this->expire;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return static::constructBaseUrl($this->url, $this->version);
    }

    /**
     * @param string $url
     * @param string $version
     * @return string
     */
    public static function constructBaseUrl($url, $version)
    {
        return $url . '/api-v' . $version . '/';
    }

    /**
     * @return bool
     */
    abstract public function login();

    /**
     * @param string $class
     * @param array|int $ids
     * @return DataObjectList
     */
    abstract public function getByIds($class, $ids);

    /**
     * @param string $class
     * @param array $record
     * @return DataObject
     */
    abstract public function createRecord($class, $record);

    /**
     * @param string $class
     * @param array $records
     * @return DataObjectList
     */
    abstract public function createRecords($class, $records);

    /**
     * @param string $class
     * @return DataObjectList
     */
    abstract public function getAll($class);

    /**
     * @param string $class
     * @param Filter $filter
     * @return DataObjectList
     */
    abstract public function getByFilter($class, Filter $filter);

    protected static function sanitizeClassName($class)
    {
        $ex = explode('\\', $class);
        return end($ex);
    }

    /**
     * @param string $url
     * @param array $params
     * @return \stdClass|array
     * @throws \Exception
     */
    protected function request($url, $params = array(), $method = 'GET')
    {
        $response = $this->getResponse($url, $params, $method);
        if ($response instanceof Error) {
            throw new Exception($response->message, $response->code);
        }
        if ($response instanceof Auth) {
            if ($response->isTokenExpired() || $response->isTokenInvalid()) {

                if (!$this->login()) {
                    throw new Exception('Could not login to ' . $url . ' as ' . $this->login);
                }
                self::saveClients();
                $response = $this->getResponse($url, $params);
                if ($response instanceof Error) {
                    throw new Exception($response->message, $response->code);
                }
                if ($response instanceof Auth) {
                    throw new Exception('Server refused with message: "' . $response->message . '"', $response->code);
                }
            }
        }
        return $response;
    }

    /**
     * @param $url
     * @param array $params
     * @return Auth|Error|\stdClass|array
     */
    protected function getResponse($url, $params = array(), $method = 'GET')
    {

        $uri = $url;
        $body = '';

        if ($method === 'GET') {
            $params['token'] = $this->token;
            $uri = $url . '?' . http_build_query($params);
        }
        if ($method === 'POST') {
            $uri = $url . '?token=' . $this->token;
            $body = json_encode($params);
        }

        $opts = array(
            'http' => array(
                'method' => $method,
                'header' => "token: {$this->token}\r\n" .
                    "Content-Type: application/json; charset=utf-8\r\n",
                'content' => $body,
            )
        );
        $context = stream_context_create($opts);

        $json = json_decode($content = file_get_contents($uri, false, $context));
        if ($json instanceof \stdClass) {
            if (isset($json->result)) {
                return Auth::create((array)$json);
            }
            if (isset($json->error)) {
                return Error::create((array)$json);
            }
        }
        return $json;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @param string $login
     */
    public function setLogin($login)
    {
        $this->login = $login;
    }

    /**
     * @param string $pass
     */
    public function setPass($pass)
    {
        $this->pass = $pass;
    }

    /**
     * @param string $expire
     */
    public function setExpire($expire)
    {
        $this->expire = $expire;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @return string
     */
    public function getPass()
    {
        return $this->pass;
    }

    /**
     * @return string
     */
    public function getExpire()
    {
        return $this->expire;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return RestfulClient
     */
    public static function getLastClient()
    {
        return self::$last_client;
    }

}
