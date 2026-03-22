<?php
require_once 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success"=>false,"message"=>"Not logged in.","redirect"=>"main.html"]);
    exit;
}

$user_id   = $_SESSION['user_id'];
$full_name = trim($_POST['full_name'] ?? '');
$username  = trim($_POST['username']  ?? '');
$email     = trim($_POST['email']     ?? '');
$phone     = trim($_POST['phone']     ?? '');
$password  = $_POST['password']       ?? '';

if (!$full_name || !$username || !$email) {
    echo json_encode(["success"=>false,"message"=>"Name, username and email are required."]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success"=>false,"message"=>"Invalid email address."]);
    exit;
}

$chk = $conn->prepare("SELECT id FROM users WHERE username=? AND id!=? LIMIT 1");
$chk->bind_param("si", $username, $user_id);
$chk->execute(); $chk->store_result();
if ($chk->num_rows > 0) {
    echo json_encode(["success"=>false,"message"=>"Username is already taken."]);
    $chk->close(); $conn->close(); exit;
}
$chk->close();
$chk2 = $conn->prepare("SELECT id FROM users WHERE email=? AND id!=? LIMIT 1");
$chk2->bind_param("si", $email, $user_id);
$chk2->execute(); $chk2->store_result();
if ($chk2->num_rows > 0) {
    echo json_encode(["success"=>false,"message"=>"Email is already in use by another account."]);
    $chk2->close(); $conn->close(); exit;
}
$chk2->close();

if ($password !== '') {
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET full_name=?,username=?,email=?,phone=?,password=? WHERE id=?");
    $stmt->bind_param("sssssi", $full_name, $username, $email, $phone, $hashed, $user_id);
} else {
    $stmt = $conn->prepare("UPDATE users SET full_name=?,username=?,email=?,phone=? WHERE id=?");
    $stmt->bind_param("ssssi", $full_name, $username, $email, $phone, $user_id);
}

if ($stmt->execute()) {
    $_SESSION['user_name'] = $full_name;
    $_SESSION['username']  = $username;
    $_SESSION['email']     = $email;
    $_SESSION['phone']     = $phone;
    echo json_encode(["success"=>true]);
} else {
    echo json_encode(["success"=>false,"message"=>"Failed to update profile."]);
}

$stmt->close(); $conn->close();
?>