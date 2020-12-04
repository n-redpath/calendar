<?php

require 'calendar_database.php';
ini_set("session.cookie_httponly", 1);
header("Content-Type: application/json");
session_start();
$json_str = file_get_contents('php://input');
$json_obj = json_decode($json_str, true);

//checks to see if the user is logged in
if (isset($_SESSION['username']) && isset($_SESSION['token'])) {
    echo json_encode(array(
        "success" => true,
        "username" => $_SESSION['username']
    ));
    exit;
}
else {
    echo json_encode(array(
        "success" => false,
    ));
    exit;
}



?>