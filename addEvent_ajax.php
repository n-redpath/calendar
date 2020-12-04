<?php

require 'calendar_database.php';

header("Content-Type: application/json");
ini_set("session.cookie_httponly", 1);

session_start();
$json_str = file_get_contents('php://input');
$json_obj = json_decode($json_str, true);

$eventName = $json_obj['name'];
$eventDate = $json_obj['date'];
$eventTime = $json_obj['time'];
$category = $json_obj['category'];
$otherUser = $json_obj['otherUser'];
$token_sent = $json_obj['token'];


$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];
$token_session = $_SESSION['token'];

if ($username != null && !hash_equals($token_sent, $token_session)) {//make sure someone is logged in
     //insert relevant information into events table
    $stmt2 = $mysqli->prepare("INSERT into events (user_ID, title, date, time, category) values (?, ?, ?, ?, ?)");
    if(!$stmt2){
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    }
    $stmt2->bind_param('issss', $user_id, $eventName, $eventDate, $eventTime, $category);
    $stmt2->execute();
    $stmt2->close();

  //we will enter this if statement if the user chose to share their event with someone else
  if($otherUser != '') {
    //get the user ID of the user we are sharing with
    $stmt_get = $mysqli->prepare("SELECT id FROM users where username=?");
    if(!$stmt_get){
      printf("Query Prep Failed: %s\n", $mysqli->error);
      exit;
  }
    $stmt_get->bind_param('s', $otherUser);
    $stmt_get->execute();
    $stmt_get->bind_result($other_user_id);
    $stmt_get->fetch();
    $stmt_get->close();

    //insert the event into the other user's calendar
    $statement = $mysqli->prepare("INSERT into events (user_ID, title, date, time, category) values (?, ?, ?, ?, ?)");
    if(!$statement){
      printf("Query Prep Failed: %s\n", $mysqli->error);
      exit;
    }
    $statement->bind_param('issss', $other_user_id, $eventName, $eventDate, $eventTime, $category);
    $statement->execute();
    $statement->close();
    echo json_encode(array(
      "message" => "Event successfully added",
      "success" => true
    ));
    exit;
  }
  echo json_encode(array(
    "success" => true
  ));
	exit;

}
else {
    echo json_encode(array(
		"success" => false,
    "message" => "Please log in"
	));
	exit;
}



?>