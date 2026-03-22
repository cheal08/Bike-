<?php
require_once 'db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) { echo json_encode(["logged_in"=>false]); exit; }
$id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id,full_name,username,email,phone,created_at FROM users WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) { session_destroy(); echo json_encode(["logged_in"=>false]); exit; }
$user = $result->fetch_assoc();
echo json_encode(["logged_in"=>true,"user"=>$user]);
$stmt->close(); $conn->close();
?>