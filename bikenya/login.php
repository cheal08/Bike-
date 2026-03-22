<?php
require_once 'db.php';
header('Content-Type: application/json');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
if (!$email || !$password) { echo json_encode(["success"=>false,"message"=>"Email and password are required."]); exit; }
$stmt = $conn->prepare("SELECT id,full_name,username,email,phone,password,created_at FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) { echo json_encode(["success"=>false,"message"=>"Invalid email or password."]); exit; }
$user = $result->fetch_assoc();
if (!password_verify($password, $user['password'])) { echo json_encode(["success"=>false,"message"=>"Invalid email or password."]); exit; }
$_SESSION['user_id']    = $user['id'];
$_SESSION['user_name']  = $user['full_name'];
$_SESSION['username']   = $user['username'];
$_SESSION['email']      = $user['email'];
$_SESSION['phone']      = $user['phone'];
$_SESSION['created_at'] = $user['created_at'];
echo json_encode(["success"=>true]);
$stmt->close(); $conn->close();
?>