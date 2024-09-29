<?php
error_reporting(E_ALL); // It's better to report all errors during development

// Database connection settings
$host = "localhost"; // Host name 
$username = "root"; // MySQL username 
$password = ""; // MySQL password 
$db_name = "bbjewels"; // Database name

// Create connection
$conn = mysqli_connect($host, $username, $password, $db_name);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
