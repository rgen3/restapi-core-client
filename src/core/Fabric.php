<?php

namespace rgen3\json\client\core;

use rgen3\json\client\core\exception;

/**
 * Class CurlFabric
 * @package Classes
 */
class Fabric
{
    /**
     * Access token if required
     * @var string|null
     */
    private static $token;

    /**
     * Debug mode
     *
     * @var bool
     */
    private $debug = false;

    /**
     * @var string
     */
    private $class;

    /**
     * @var array
     */
    private $options;

    public function __construct($class, $options, $debug = false)
    {
        $this->class = $class;
        $this->options = $options;
        $this->debug = $debug;

        if (!class_exists($this->class))
        {
            throw new UndefinedMethod("Undefined method {$this->class}");
        }
    }

    /**
     * Sets an access token
     *
     * @param $token
     */
    public static function setToken($token)
    {
        self::$token = $token;
    }

    /**
     * Gets an access token
     *
     * @return string
     */
    public static function getToken()
    {
        return self::$token;
    }

    /**
     * Disables debug
     *
     * @return $this
     */
    public function disableDebug()
    {
        $this->debug = true;
        return $this;
    }

    /**
     * Enables debug
     */
    public function enableDebug()
    {
        $this->debug = false;
        return $this;
    }

    /**
     * Executes a request
     *
     * @return mixed
     */
    public function exec()
    {
        /** @var IMethod $class */
        $class = new $this->class;
        $class->setResponseData($this->options);

        $request = new Curl($class, $class->tokenRequired() ? self::$token : null);
        $request->type()->setTrace($this->debug)->request();
        $class->setCurl($request);
        return $class;
    }
}