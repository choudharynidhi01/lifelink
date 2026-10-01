<?php
$host     = "127.0.0.1";
$username = "root";
$password = "";
$dbname   = "lifelink_db";
$port     = 3306;

$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}
?>