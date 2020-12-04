<?php

require 'calendar_database.php';

ini_set("session.cookie_httponly", 1);

header("Content-Type: application/json"); 

//getting the info from the php script
$json_str = file_get_contents('php://input');
$json_obj = json_decode($json_str, true);

//getting the credentials from JSON
$username = $json_obj['username'];
$password = $json_obj['password'];


//hashing the password input to be stored in the database
$hash_pass = password_hash($password, PASSWORD_BCRYPT); #this password is now salty hashed

//seeing if the username already exists
$statement = $mysqli->prepare("SELECT COUNT(*) FROM users where username=?");
$statement->bind_param('s', $username);
$statement->execute();
$statement->bind_result($row_cnt);
$statement->fetch();


if($row_cnt == null) {//if the username doesn't exist
    $statement->close();

    //preparing the mysql statement
    $stmt = $mysqli->prepare("INSERT into users (username, password) values (?, ?)");

    if(!$stmt){
        echo json_encode(array(
            "success" => false,
        ));
	    exit;
    }




    //binding the parameters and executing the statement
    $stmt->bind_param('ss', $username, $hash_pass);

    $stmt->execute();
    $stmt->close();

    echo json_encode(array(//sending the data back
        "success" => true,
        "num" => $row_cnt,
    ));
    exit;

    }
    else {//the username already exists
        echo json_encode(array(
            //something to see if the statement doesnt do anything
            "success" => false,
        ));
        exit;


}
