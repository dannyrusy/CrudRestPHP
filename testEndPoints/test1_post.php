<?php
	
	$dataPost = file_get_contents("php://input");
	/*
	 $response = array();
	 array_push($response, "TEST1");
	 array_push($response, "OK");
	 echo json_encode($response);
	 */
	/*
	$response = array();
	foreach ($dataPost as $key => $value) {
		array_push($response, $key."-".$value);
	}
	*/
	$response = json_decode($dataPost);
	
	echo json_encode($response);
	
?>