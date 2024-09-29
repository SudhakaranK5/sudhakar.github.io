<?php
$db_username = 'root';
$db_password = '';
$db_name = 'bbjewels';
$db_host = 'localhost';
$item_per_page = 8;

// Create connection
$db_conx = mysqli_connect($db_host, $db_username, $db_password, $db_name);

// Check connection
if (!$db_conx) {
    die('Connection failed: ' . mysqli_connect_error());
}
?>
