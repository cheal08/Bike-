<?php
require_once 'db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) { echo json_encode(["success"=>false,"redirect"=>"main.html"]); exit; }
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id,full_name,username,email,phone,created_at FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
$stmt = $conn->prepare("SELECT transaction_id,bike_name,ride_date,ride_time,amount,payment_method,status,created_at FROM reservations WHERE user_id=? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$reservations = [];
while ($row = $result->fetch_assoc()) { $reservations[] = $row; }
$stmt->close(); $conn->close();
echo json_encode(["success"=>true,"user"=>$user,"reservations"=>$reservations]);
?>