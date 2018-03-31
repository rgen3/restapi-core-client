<?php

namespace rgen3\json\client\core;

use \rgen3\json\client\exception\CurlIsAsyncException;

class Curl {

    public $defaultCurlTimeout = 30;

    public $token;

    public $options = array(
        CURLOPT_RETURNTRANSFER => true,
    );

    /**
     * @var IMethod
     */
    private $method;

    /**
     * @var resource cURL
     */
    private $curl;

    /**
     * @var bool
     */
    private $trace = false;

    /**
     * @var array
     */
    private $responseCurlHeaders;

    /**
     * @var array
     */
    private $requestCurlHeaders;

    /**
     * Curl constructor.
     * @param IMethod $method
     * @param null $token
     */
    public function __construct(IMethod $method, $token = null)
    {
        $this->method = $method;
        $this->curl = curl_init();
        $this->options = array_replace($this->options, $method->getCurlOptions());
        $this->token = $token;

        $this->setHeader('X-Request-Id',$this->generateXRequestId());
    }

    /**
     * @see http://php.net/manual/ru/function.com-create-guid.php#99425
     * @return string
     */
    private function generateXRequestId()
    {
        return sprintf(
            '%04X%04X-%04X-%04X-%04X-%04X%04X%04X',
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(16384, 20479),
            mt_rand(32768, 49151),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535)
        );
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     *
     */
    public function __invoke()
    {
        // TODO: Implement __invoke() method.
    }

    /**
     * @return Curl
     */
    public function type()
    {
        if ($this->method->waitAnswer())
        {
            return $this->sync();
        }

        return $this->async();
    }

    /**
     * @return $this
     */
    public function async()
    {
        $this->options[CURLOPT_RETURNTRANSFER] = false;
        curl_setopt($this->curl, CURLOPT_TIMEOUT, 1);
        curl_setopt($this->curl, CURLOPT_NOSIGNAL, 1);
        return $this;
    }

    /**
     * @return $this
     */
    public function sync()
    {
        curl_setopt($this->curl, CURLOPT_NOSIGNAL, 0);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, $this->defaultCurlTimeout);

        return $this;
    }

    /**
     * @return $this
     */
    public function enableTrace()
    {
        $this->trace = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function disableTrace()
    {
        $this->trace = false;
        return $this;
    }

    /**
     * @return bool
     */
    public function traceEnabled()
    {
        return (bool) $this->trace;
    }

    /**
     * @param $trace
     * @return $this
     */
    public function setTrace($trace)
    {
        $this->trace = $trace;
        return $this;
    }

    /**
     * @return mixed
     */
    public function returnTransfer()
    {
        return $this->options[CURLOPT_RETURNTRANSFER];
    }

    /**
     *
     */
    public function reset()
    {
        curl_reset($this->curl);
    }

    /**
     * @return mixed|null
     */
    public function getResult()
    {
        if ($this->traceEnabled())
        {
            var_dump(curl_getinfo($this->curl));
        }

        $data = curl_exec($this->curl);

        if ($this->returnTransfer())
        {
            return $data;
        }

        return null;
    }

    /**
     * @return string
     */
    public function prepareUrl()
    {
        $get = array();
        if ($this->method->tokenRequired()) {
            $get = array($this->method->getTokenKey() => Fabric::getToken());
        }

        if ($this->isGet()) {
            $get = array_merge($get, $this->method->getData());
        }

        $get = '?' . http_build_query($get);

        $url = trim($this->method->getBaseUrl(), '/') . '/' . trim($this->method->getUrlPath(), '/') . $get;

        return $url;
    }

    /**
     * @return $this
     */
    public function setCurlData()
    {
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, strtoupper($this->method->getType()));
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->method->getRequestData());

        return $this;
    }

    /**
     * @param $name
     * @param $header
     * @return $this
     */
    public function setHeader($name, $header)
    {
        $this->requestCurlHeaders[$name] = sprintf('%s: %s', $name, $header);
        return $this;
    }

    /**
     * @return bool
     */
    public function isGet()
    {
        return strtolower($this->method->getType()) === 'get';
    }

    /**
     * @return mixed
     */
    public function request()
    {
        if ($this->traceEnabled())
        {
            var_dump(curl_version());
        }

        curl_setopt($this->curl, CURLOPT_URL, $this->prepareUrl());
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, $this->options[CURLOPT_RETURNTRANSFER]);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array_values($this->requestCurlHeaders));
        curl_setopt($this->curl, CURLOPT_HEADER, true);

        if (!$this->isGet())
        {
            $this->setCurlData();
        }

        $curlResponse = $this->getResult();
        $body = $this->prepareCurlResponseAndGetBody($curlResponse);

        return $this->method->processResult($body)->getResult();
    }

    public function getResponseHeaders()
    {
        if (!$this->returnTransfer()) {
            throw new CurlIsAsyncException('You cannot get response headers for asyn curl');
        }
        return $this->responseCurlHeaders;
    }

    /**
     * @param $response
     * @return string|null
     */
    private function prepareCurlResponseAndGetBody($response)
    {
        $headerSize = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
        $this->responseCurlHeaders = $this->parseCurlHeaders(substr($response, 0, $headerSize));
        return substr($response, $headerSize);
    }

    private function parseCurlHeaders($headers)
    {
        $result = [];
        foreach (explode("\r\n", trim($headers)) as $i => $line)
        {
            if ($i === 0) {
                $line = explode(' ', $line);
                $result['protocol'] = $line[0];
                $result['httpCode'] = $line[1];
                $result['status'] = $line[2];
            } else {
                list ($key, $value) = explode(': ', $line);
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     *
     */
    private function close()
    {
        curl_close($this->curl);
    }
}