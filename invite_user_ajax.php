<?php
//
require 'calendar_database.php';
ini_set("session.cookie_httponly", 1);

session_start();
header("Content-Type: application/json"); 

//getting the info from the php script
$json_str = file_get_contents('php://input');
$json_obj = json_decode($json_str, true);

$otherUser = $json_obj['otherUser'];
$eventName = $json_obj['name'];
$eventDate = $json_obj['date'];
$eventTime = $json_obj['time'];
$category = $json_obj['category'];



?>