<?php
require 'calendar_database.php';

//get the user id of the original user it was shared with
//select the event id of the shared event
//make all the same changes to the shared event
header("Content-Type: application/json");
session_start();
$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

$json_str = file_get_contents('php://input');
$json_obj = json_decode($json_str, true);

//the original stuff
$eventNameO = $json_obj['original name'];
$eventDateO = $json_obj['original date'];
$eventTimeO = $json_obj['original time'];
$eventCategoryO = $json_obj['original category'];
$sharedUserO = $json_obj['original shared user'];

//new stuff
$eventNameN = $json_obj['new name'];
$eventDateN = $json_obj['new date'];
$eventTimeN = $json_obj['new time'];
$eventCategoryN = $json_obj['new category'];
$sharedUserN = $json_obj['new shared user'];

//variables for data to send back, only will be changed if user inputted a nonempty string
//for testing purposes
$nameSendBack = $eventNameO;
$dateSendBack = $eventDateO;
$timeSendBack = $eventTimeO;
$categorySendBack = $eventCategoryO;
$sharedUserSendBack = '';

//token stuff
$token_sent = $json_obj['token'];
$token_session = $_SESSION['token'];

//make an edit statement for each field, given that it is not null
//check to see if someone is logged in
if ($username != null && !hash_equals($token_sent, $token_session)) {
    //get event ID
    $stmt = $mysqli->prepare("SELECT id FROM events WHERE (user_ID=? AND title=?) AND date=?");
    if (!$stmt) {
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    }
    $stmt->bind_param('iss', $user_id, $eventNameO, $eventDateO);
    $stmt->execute();
    $stmt->bind_result($event_id);
    $stmt->fetch();
    $stmt->close();
    //if the user wants to change the info of the shared event on the other user's calendar
    if ($sharedUserO != '') {
        //get the id of the shared user
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE username=?");
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $mysqli->error);
            exit;
        }
        $stmt->bind_param('s', $sharedUserO);
        $stmt->execute();
        $stmt->bind_result($sharedUserO_id);
        $stmt->fetch();
        $stmt->close();
        //get the id of the event in the shared user's calendar
        $stmt = $mysqli->prepare("SELECT id FROM events WHERE (user_ID=? AND title=?) AND date=?");
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $mysqli->error);
            exit;
        }
        $stmt->bind_param('iss', $sharedUserO_id, $eventNameO, $eventDateO);
        $stmt->execute();
        $stmt->bind_result($sharedEvent_id);
        $stmt->fetch();
        $stmt->close();
    }

    if ($event_id != null) {
        //edit name
        if ($eventNameN != '') { //if an name to change is inputted
            $stmt = $mysqli->prepare("UPDATE events SET title=? WHERE id=?");
            if (!$stmt) {
                printf("Query Prep Failed: %s\n", $mysqli->error);
                exit;
            }
            $stmt->bind_param('si', $eventNameN, $event_id);
            $stmt->execute();
            $stmt->close();
            $nameSendBack = $eventNameN; //send back the new name
            //if the event was shared
            if($sharedEvent_id != null) {
                $stmt = $mysqli->prepare("UPDATE events SET title=? WHERE id=?");
                if (!$stmt) {
                    printf("Query Prep Failed: %s\n", $mysqli->error);
                    exit;
                }
                $stmt->bind_param('si', $eventNameN, $sharedEvent_id);
                $stmt->execute();
                $stmt->close();
            }
        }
        //edit date
        if ($eventDateN != '') { //if an date to change is inputted
            $stmt = $mysqli->prepare("UPDATE events set date=? where id=?");
            if (!$stmt) {
                printf("Query Prep Failed: %s\n", $mysqli->error);
                exit;
            }
            $stmt->bind_param('si', $eventDateN, $event_id);
            $stmt->execute();
            $stmt->close();
            $dateSendBack = $eventDateN; //send back the new date
            //if the event was shared  
            if ($sharedEvent_id != null) {
                $stmt = $mysqli->prepare("UPDATE events set date=? where id=?");
                if (!$stmt) {
                    printf("Query Prep Failed: %s\n", $mysqli->error);
                    exit;
                }
                $stmt->bind_param('si', $eventDateN, $sharedEvent_id);
                $stmt->execute();
                $stmt->close();
            }
        }
        //edit time
        if ($eventTimeN != '') { //if an time to change is inputted
            $stmt = $mysqli->prepare("UPDATE events set time=? where id=?");
            if (!$stmt) {
                printf("Query Prep Failed: %s\n", $mysqli->error);
                exit;
            }
            $stmt->bind_param('si', $eventTimeN, $event_id);
            $stmt->execute();
            $stmt->close();
            $timeSendBack = $eventTimeN; //send back the new time
            //if the event shared
            if ($sharedEvent_id != null) {
                $stmt = $mysqli->prepare("UPDATE events set time=? where id=?");
                if (!$stmt) {
                    printf("Query Prep Failed: %s\n", $mysqli->error);
                    exit;
                }
                $stmt->bind_param('si', $eventTimeN, $sharedEvent_id);
                $stmt->execute();
                $stmt->close();
            }
        }
        //edit category
        if ($eventCategoryN != '') { //if an category to change is inputted
            $stmt = $mysqli->prepare("UPDATE events set category=? where id=?");
            if (!$stmt) {
                printf("Query Prep Failed: %s\n", $mysqli->error);
                exit;
            }
            $stmt->bind_param('si', $eventCategoryN, $event_id);
            $stmt->execute();
            $stmt->close();
            $categorySendBack = $eventCategoryN; //send back the new category

            if ($sharedEvent_id != null) {
                $stmt = $mysqli->prepare("UPDATE events set category=? where id=?");
                if (!$stmt) {
                    printf("Query Prep Failed: %s\n", $mysqli->error);
                    exit;
                }
                $stmt->bind_param('si', $eventCategoryN, $sharedEvent_id);
                $stmt->execute();
                $stmt->close();
            }
        }
        if ($sharedUserN != '') { //if they want to share an event with another user
            //insert all of the event info into the other users database
            //get the user_ID of the inputted username
            $stmt_get = $mysqli->prepare("SELECT id FROM users where username=?");
            if (!$stmt_get) {
                printf("Query Prep Failed: %s\n", $mysqli->error);
                exit;
            }
            $stmt_get->bind_param('s', $sharedUserN);
            $stmt_get->execute();
            $stmt_get->bind_result($other_user_id);
            $stmt_get->fetch();
            $stmt_get->close();

            //insert the event into the other user's calendar
            $statement = $mysqli->prepare("INSERT into events (user_ID, title, date, time, category) values (?, ?, ?, ?, ?)");
            if (!$statement) {
                printf("Query Prep Failed: %s\n", $mysqli->error);
                exit;
            }
            $statement->bind_param('issss', $other_user_id, $nameSendBack, $dateSendBack, $timeSendBack, $categorySendBack);
            $statement->execute();
            $statement->close();
        }
    }
}


echo json_encode(array(
    "success" => true

));
exit;
