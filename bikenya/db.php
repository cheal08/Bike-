<?php
session_start();
$host   = "localhost";
$user   = "root";
$pass   = "";
$dbname = "bikeniya";
$conn   = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "DB connection failed: " . $conn->connect_error]));
}
$conn->set_charset("utf8");
?>