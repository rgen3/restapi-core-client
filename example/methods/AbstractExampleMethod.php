<?php

use rgen3\json\client\core\AbstractMethod;

abstract class AbstractExampleMethod extends AbstractMethod
{
    public function getBaseUrl()
    {
        return 'https://httpbin.org';
    }
}