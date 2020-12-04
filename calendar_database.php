<?php
// linking in the calendar database
$mysqli = new mysqli('localhost', 'sammkaiser2', 'cheese', 'calendar');

if($mysqli->connect_errno) {
	printf("Connection Failed: %s\n", $mysqli->connect_error);
	exit;
}
?>
