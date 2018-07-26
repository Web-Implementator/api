<?php

$host = 'localhost';
$db_name = 'test';
$db_user = '';
$db_pass = '';
 
$mysqli = new mysqli($host, $db_user, $db_pass, $db_name);
 
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}