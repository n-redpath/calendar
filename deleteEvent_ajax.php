<?php
require 'calendar_database.php';
ini_set("session.cookie_httponly", 1);

header("Content-Type: application/json"); 
session_start();

$json_str = file_get_contents('php://input');
$json_obj = json_decode($json_str, true);

$eventName = $json_obj['name'];
$eventDate = $json_obj['date'];
$eventTime = $json_obj['time'];

$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

//token stuff
$token_sent = $json_obj['token'];
$token_session = $_SESSION['token'];

if ($username != null && !hash_equals($token_sent, $token_session)) {
//getting the event id of the user who wants to delete their event, as well as the title of the event
$stmt = $mysqli->prepare("SELECT id FROM events WHERE (user_ID=? AND title=?) AND date=?");
if(!$stmt){
    printf("Query Prep Failed: %s\n", $mysqli->error);
    exit;
}
$stmt->bind_param('iss', $user_id, $eventName, $eventDate);
$stmt->execute();
$stmt->bind_result($event_id);
$stmt->fetch();
$stmt->close();

    //delete relevant information from events table
   $stmt2 = $mysqli->prepare("DELETE from events WHERE id=?");
   if(!$stmt2){
       printf("Query Prep Failed: %s\n", $mysqli->error);
       exit;
   }
   $stmt2->bind_param('i', $event_id);
   $stmt2->execute();
   $stmt2->close();
   echo json_encode(array(
       "name" => $eventName,
       "id" => $event_id,
       "date" => $eventDate,
       "message" => "Event successfully deleted"
   ));
   exit;
}

?>