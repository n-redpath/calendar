<?php
require 'calendar_database.php';
ini_set("session.cookie_httponly", 1);

session_start();
header("Content-Type: application/json"); 

//getting the info from the php script
$json_str = file_get_contents('php://input');
$json_obj = json_decode($json_str, true);

//getting the credentials from JSON
$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

$category = $json_obj['category'];

if($category != '') {
    //getting the events that match the user ID and the desired category
    $statement = $mysqli->prepare("SELECT * FROM events WHERE user_ID=? AND category=?");
    if(!$statement){
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    }
    $statement->bind_param('is', $user_id, $category);
    $statement->execute();
    $result = $statement->get_result();

    //putting the events into a usable format
    $event_array = array();
    $i=0;
    while($row = $result->fetch_assoc()) {
        $event_array[$i] = $row; 
        $i++;
    }
    $statement->fetch();
    $statement->close();

    //sending the events back
    echo json_encode(array(
        "events" => $event_array,
        "success" => true
    ));
    exit;
}
exit;

?>