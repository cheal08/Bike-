<?php
require_once 'db.php';
header('Content-Type: application/json');

$email = trim($_POST['email'] ?? '');
$code  = trim($_POST['code']  ?? '');

if (!$email || !$code) {
    echo json_encode(['success' => false, 'message' => 'Email and code are required.']);
    exit;
}

$stmt = $conn->prepare(
    "SELECT id, code, full_name, phone, hashed_password, expires_at
     FROM email_verifications
     WHERE email = ?
     ORDER BY id DESC
     LIMIT 1"
);
$stmt->bind_param('s', $email);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    echo json_encode(['success' => false, 'message' => 'No verification request found for this email. Please try again.']);
    $conn->close();
    exit;
}

if (strtotime($row['expires_at']) < time()) {
    $conn->query("DELETE FROM email_verifications WHERE email = '" . $conn->real_escape_string($email) . "'");
    echo json_encode(['success' => false, 'message' => 'Code has expired. Please request a new one.']);
    $conn->close();
    exit;
}

if ($row['code'] !== $code) {
    echo json_encode(['success' => false, 'message' => 'Invalid code. Please try again.']);
    $conn->close();
    exit;
}

$base_username = strtolower(str_replace(' ', '', $row['full_name']));
$username      = $base_username;
$suffix        = 1;

while (true) {
    $uq = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
    $uq->bind_param('s', $username);
    $uq->execute();
    $uq->store_result();
    if ($uq->num_rows === 0) { $uq->close(); break; }
    $uq->close();
    $username = $base_username . $suffix++;
}

$ins = $conn->prepare(
    "INSERT INTO users (full_name, username, email, phone, password)
     VALUES (?, ?, ?, ?, ?)"
);
$ins->bind_param(
    'sssss',
    $row['full_name'],
    $username,
    $email,
    $row['phone'],
    $row['hashed_password']
);

if (!$ins->execute()) {
    echo json_encode(['success' => false, 'message' => 'Could not create account. The email may already be registered.']);
    $ins->close(); $conn->close();
    exit;
}

$new_user_id = $conn->insert_id;
$ins->close();

$conn->query("DELETE FROM email_verifications WHERE email = '" . $conn->real_escape_string($email) . "'");

 $_SESSION['user_id']    = $new_user_id;
$_SESSION['user_name']  = $row['full_name'];
$_SESSION['username']   = $username;
$_SESSION['email']      = $email;
$_SESSION['phone']      = $row['phone'];
$_SESSION['created_at'] = date('Y-m-d H:i:s');

$conn->close();
echo json_encode(['success' => true, 'message' => 'Account created and verified successfully!']);