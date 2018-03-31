<?php

class Asynchronous extends AbstractExampleMethod
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
            'sleep' => '50000'
        ];
    }

    public function waitAnswer()
    {
        return false;
    }
}