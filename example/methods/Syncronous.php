<?php

class Syncronous extends AbstractExampleMethod
{
    public function getUrlPath()
    {
        return '/post';
    }

    public function getData()
    {
        return [
            'key' => 'value'
        ];
    }

    public function tokenRequired()
    {
        return false;
    }
}