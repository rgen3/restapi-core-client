<?php

namespace rgen3\json\client\core;

interface IMethod
{
    /**
     * Sets the data from a curl request
     *
     * @param $data
     * @return mixed
     */
    public function setResponseData($data);

    /**
     * Makes request asynchronous
     *
     * You woun't waste time waiting for a request answer
     * But you will not get any response
     * for an answer,
     *
     * @return mixed
     */
    public function waitAnswer();

    /**
     * Sets the type of the request
     * i.e. 'GET', 'POST', 'PUT', 'DELETE', etc.
     *
     * @return mixed
     */
    public function getType();

    /**
     * Sets the url of a method to be call
     *
     * @return mixed
     */
    public function getUrlPath();

    /**
     * Returns the data to be send
     *
     * @return mixed
     */
    public function getData();

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
    public function getResult();

    /**
     * @return array
     */
    public function getCurlOptions();

    /**
     * @return bool
     */
    public function tokenRequired();

    /**
     * Returns token key
     * @return string
     */
    public function getTokenKey();

    /**
     *
     */
     public function setCurl(Curl $curl);

     /**
      * Prepares data to be sent
      * For all method except GET
      *
      */
     public function getRequestData();
}