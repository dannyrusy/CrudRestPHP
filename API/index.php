<?php

    require __DIR__.'/CustomWS.php';
    require __DIR__ .'/JWTGenerator.php';
    require __DIR__.'/inc/config.inc.php';
	
    //some php settings
    error_reporting(E_ALL);
    set_time_limit(0);
    ini_set("memory_limit","-1");
    
    //List of endpoints... the format is easy:
    // - name ==> name of the endpoint
    // - method ==> method enabled for the endpoint
    // - url ==> can be php or other
    $endpoints = array(
    	array("name" => "test1", "method" => "GET", "url" => "http://localhost/CrudRestPHP/testEndPoints/test1.php"),
    	array("name" => "test2", "method" => "GET", "url" => "http://localhost/CrudRestPHP/testEndPoints/test2.php"),
    	array("name" => "test3", "method" => "POST", "url" => "http://localhost/CrudRestPHP/testEndPoints/test1_post.php"),
    	array("name" => "test4", "method" => "DELETE", "url" => "http://localhost/CrudRestPHP/testEndPoints/test4_delete.php")
    );
    
    //create the authenticator object (for the creation of token)
    $authenticator = new JWTGenerator($secret_key, $expire_after);
    //object for manage the endpoints
    $AWS = new CustomWS($authenticator, $endpoints);
    $AWS->handle();

?>