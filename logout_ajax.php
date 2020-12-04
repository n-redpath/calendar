<?php

//destroying the current session
session_start();
ini_set("session.cookie_httponly", 1);

session_destroy();
echo json_encode( array(//sending the data back
    "success" => true,
));

exit;

?>