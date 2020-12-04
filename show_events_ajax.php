<?php
require 'calendar_database.php';
ini_set("session.cookie_httponly", 1);

session_start();
header("Content-Type: application/json"); 


//getting the credentials from JSON
$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

//getting all of the events associated with a specific user ID
$statement = $mysqli->prepare("SELECT * FROM events WHERE user_ID=?");
if(!$statement){
    printf("Query Prep Failed: %s\n", $mysqli->error);
    exit;
}
$statement->bind_param('i', $user_id);
$statement->execute();
$result = $statement->get_result();

//putting the events in a usable format
$event_array = array();
$i=0;
while($row = $result->fetch_assoc()) {
    $event_array[$i] = $row; 
    $i++;
}
$statement->fetch();
$statement->close();
//sending the data back
echo json_encode(array(
    "events" => $event_array,
    "success" => true
));
exit;


?>