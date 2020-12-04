<?php
// login_ajax.php

header("Content-Type: application/json"); 
ini_set("session.cookie_httponly", 1);
//session_start();
$u = $_SESSION['username'];

require 'calendar_database.php';
//Because you are posting the data via fetch(), php has to retrieve it elsewhere.
$json_str = file_get_contents('php://input');
//This will store the data into an associative array
$json_obj = json_decode($json_str, true);

//Variables can be accessed as such:
$username = $json_obj['username'];
$password = $json_obj['password'];
//This is equivalent to what you previously did with $_POST['username'] and $_POST['password']


// Check to see if the username and password are valid. 
$stmt = $mysqli->prepare("SELECT COUNT(*), password, id FROM users WHERE username =?");
$stmt->bind_param('s', $username);
$stmt->execute();
$stmt->bind_result($cnt, $actual_password, $user_id);
$stmt->fetch();
$stmt->close();


if ($cnt == 1 && password_verify($password, $actual_password)) {//only one row returned and the passwords match
	//setting the session variables
	session_start();
	$_SESSION['username'] = $username;
	$_SESSION['user_id'] = $user_id;
	$_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes(32));
	$token = $_SESSION['token'];
	//sending the information back
	echo json_encode(array(
		"success" => true,
		"username" => $username,
		"test" => $u,
		"token" => $token
	));
	exit;
} else {
	echo json_encode(array(
		"success" => false,
		"message" => "Incorrect Username or Password"
	));
	exit;
}
