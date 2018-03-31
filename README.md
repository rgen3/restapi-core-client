# yii2-json-api-controller
This repository provide simple curl usage

# Installation

```
composer require rgen3/restapi-core-client
```

Main features
---

- json requests as default
- generates guid for every request passing as X-Request-Id header
- possibility to make async curl requests
- easy adding curl options
- simple data providing
- one class for one method
- support for access-token passing as a get parameter

Usage example
---

Code example usage is in `example` folder

```php
<?php

use rgen3\json\client\core\AbstractMethod;

class TestRequest extends AbstractMethod
{
    public function getBaseUrl()
    {
        return 'https://httpstat.us';
    }

    public function getUrlPath()
    {
        return '/200';
    }

    public function getType()
    {
        return 'GET';
    }

    public function getData()
    {
        // Sends GET data to sleep 50 seconds
        return [
            'sleep' => '2000'
        ];
    }

    public function waitAnswer()
    {
        // if you do not want to wait for an answer
        // set here false
        return true;
    }
}

```

After creating file call it in your code
```php
// data you want to send
$data = [
    'any' => 'array'
];
$request = (new TestRequest($data))->executer();

$request->getResult();
// and same method
$request->result;

// Get curl response headers
$request->getCurl()->getResponseHeaders();

// Get response status
$request->getResponseStatus();
```

if you want to provide token as a GET parameter than you have to use
```php
rgen3\json\client\core\Fabric::setToken('needed-token');
```
before any method requiring the token called


Note you cannot use methods related with curl response when you are using async mode
(when `waitAnswer` returns false)

Methods
---

You can use any method below in your `TestRequest` class to modify behaviour

```php

/**
 * Makes request asynchronous
 *
 * You woun't waste time waiting for a request answer
 * But you will not get any response
 * for an answer,
 *
 * @return bool
 */
public function waitAnswer()
{
    // if you do not want to wait for an answer
    // set return value to `false`
    return true;
}

/**
 * Sets the type of the request
 * i.e. 'GET', 'POST', 'PUT', 'DELETE', etc.
 *
 * @return mixed
 */
public function getType()
{
    // Default value to be returned
    return 'POST';
}

public function getBaseUrl()
{
    // base url you want to use for this method
    return 'https://httpstat.us';
}

/**
 * Sets the url of a method to be call
 *
 * @return mixed
 */
public function getUrlPath()
{
    // path without url
    // slashes will be trimmed
    return '/pathname';
}

/**
 * Returns the data to be send
 *
 * @return mixed
 */
public function getData()
{
    // return any data you want
    // as default you have to json compatible data
    // because of json_encode method in `AbstractMethod` class
    return [];
}

/**
 * Processes
 *
 * @param $data
 * @return IMethod
 */
public function processResult($data);

/**
 * Returns request results
 * @return mixed
 */
public function getResult()
{
    // returns result from curl
    return [];
}

/**
 * @return array
 */
public function getCurlOptions()
{
    // Here you can provide additional curl parameters
    return [];
}

/**
 * @return bool
 */
public function tokenRequired() 
{
    // return true if you want to send token as get parameter
    return true;
}

/**
 * Returns token key
 * @return string
 */
public function getTokenKey()
{
    // You can set any value you want to see in GET query parameter for token
    // default is
    return 'access-token';
}


/**
 * Prepares data to be sent
 * For all method except GET
 * @return array
 */
public function getRequestData()
{
   $array = [];
   // any code you want
   return $array;
}
 ```