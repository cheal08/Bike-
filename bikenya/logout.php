<?php
require_once 'db.php';
session_destroy();
header('Content-Type: application/json');
echo json_encode(["success"=>true]);
?>