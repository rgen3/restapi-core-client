<?php

set_include_path('./methods');
// requires all necessary files
spl_autoload_register(function($class) {
    $class = str_replace('rgen3\\json\\client\\core\\', '../src/core/', $class);
    $class = str_replace('rgen3\\json\\client\\exception\\', '../src/exception/', $class);
    require $class . '.php';
});

/** @var \rgen3\json\client\core\IMethod $request */
$request = (new Syncronous([
    'message' => 'Any data of yours'
]))->execute();

//var_dump($request->getCurl()->getResponseHeaders());
//var_dump($request->getResponseStatus());
var_dump($request->getResult());
die();
try {
    $request = (new Asynchronous([
        'any data'
    ]))->execute();

    var_dump($request->getResult());
    var_dump($request->getResponseStatus());
    var_dump($request->getCurl()->getResponseHeaders());
} catch (Exception $e) {
    echo $e->getMessage();
}
