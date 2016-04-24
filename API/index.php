<?php

    require __DIR__.'/CustomWS.php';
    require __DIR__ .'/JWTGenerator.php';
    require __DIR__.'/inc/config.inc.php';

    error_reporting(E_ALL);
    set_time_limit(0);
    ini_set("memory_limit","-1");

    $endpoints = array(
        'test1'                         => "http://localhost/CrudRestPHP/testEndPoints/test1.php",
        'test2'                         => "http://localhost/CrudRestPHP/testEndPoints/test2.php"
    );

    $authenticator = new JWTGenerator($secret_key, $expire_after);
    $AWS = new CustomWS($authenticator, $endpoints);
    $AWS->handle();

?>