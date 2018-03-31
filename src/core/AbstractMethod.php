<?php

namespace rgen3\json\client\core;

use rgen3\json\client\exception\CurlIsAsyncException;

abstract class AbstractMethod implements IMethod
{
    /**
     * @var Curl
     */
    private $curl;

    /**
     * Data to be sent
     *
     * @var array
     */
    private $data;

    /**
     * Debug mode
     *
     * @var bool
     */
    private $debug = false;

    /**
     * Contains response data
     *
     * @var array
     */
    private $result;

    /**
     * Returns the url of a request
     *
     * @return string
     */
    abstract public function getBaseUrl();

    /**
     * AbstractMethod constructor.
     *
     * @param array $data
     * @param bool $debug
     */
    public function __construct($data = [], $debug = false)
    {
        $this->data = $data;
        $this->debug = $debug;
    }


    /**
     * Helper method for request execution
     * @return Curl
     * @throws \Exception
     */
    public function execute()
    {
        return (new Fabric(static::class, $this->data, $this->debug))->exec();
    }

    /**
     * @param Curl $curl
     * @return $this
     */
    final public function setCurl(Curl $curl)
    {
        $this->curl = $curl;
        return $this;
    }

    /**
     * Request type can be override in child method
     * @return mixed|string
     */
    public function getType()
    {
        return 'POST';
    }

    /**
     * Enables or disables debug mode
     *
     * @param $debug
     * @return $this
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;

        return $this;
    }

    /**
     * Enables debug mode
     *
     * @return $this
     */
    public function enableDebug()
    {
        $this->debug = true;
        return $this;
    }

    /**
     * Disables debug mode
     *
     * @return $this
     */
    public function disableDebug()
    {
        $this->debug = false;
        return $this;
    }

    /**
     * Curl class uses this method to set the response
     *
     * @param $result
     * @return $this
     */
    public function setResult($result)
    {
        $this->result = $result;
        return $this;
    }

    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return mixed|string
     */
    public function getRequestId()
    {
        return $this->result['request-id'] ?? '';
    }

    /**
     * Response status
     *
     * @return array
     * @throws CurlIsAsyncException
     */
    public function getResponseStatus()
    {
        if (!$this->waitAnswer()) {
            throw new CurlIsAsyncException('You cannot get response status for async request');
        }
        return [
            'statusCode' => $this->curl->getResponseHeaders()['httpCode'],
            'statusText' => $this->curl->getResponseHeaders()['status'],
        ];
    }

    /**
     * Is response ended with error
     *
     * @return bool
     */
    public function isError()
    {
        return $this->result['status'] === 'error';
    }

    /**
     * Returns errors from the request
     *
     * @return array
     */
    public function getErrorData()
    {
        return $this->isError() ? $this->getResult() : [];
    }

    /**
     * Sets data to from curl request
     *
     * @param $data
     * @return $this|mixed
     */
    public function setResponseData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Gets the data to be sent
     *
     * @return array|mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Sets if it is necessary to wait for a response answer
     *
     * @return bool|mixed
     */
    public function waitAnswer()
    {
        return true;
    }

    /**
     * Handle result preparation
     * You can override it in child class
     *
     * @param $data
     * @return $this
     */
    public function processResult($data)
    {
        $data = json_decode($data, true);
        $this->setResult($data);
        return $this;
    }

    /**
     * Returns additional options to be used in curl
     *
     * @return array
     */
    public function getCurlOptions()
    {
        return [];
    }

    /**
     * @return bool|void
     */
    public function tokenRequired()
    {
        // TODO: Implement tokenRequired() method.
    }

    /**
     * @return string
     */
    public function getTokenKey()
    {
        return 'access-token';
    }

    /**
     * @return Curl
     */
    final public function getCurl()
    {
        return $this->curl;
    }

    /**
     * Prepares data to be sent
     * For all type of request except GET
     *
     * @return string
     */
    public function getRequestData()
    {
        return json_encode(['data' => $this->getData()], JSON_UNESCAPED_UNICODE | JSON_BIGINT_AS_STRING);
    }
}