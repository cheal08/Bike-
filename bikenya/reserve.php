<?php
require_once 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success"=>false,"message"=>"Not logged in.","redirect"=>"main.html"]);
    exit;
}

$user_id        = $_SESSION['user_id'];
$bike_name      = trim($_POST['bike_name']      ?? '');
$ride_date      = trim($_POST['ride_date']      ?? '');
$ride_time      = trim($_POST['ride_time']      ?? '');
$amount         = floatval($_POST['amount']     ?? 0);
$payment_method = trim($_POST['payment_method'] ?? '');
$rider_name     = trim($_POST['rider_name']     ?? '');

if (!$bike_name || !$ride_date || !$ride_time || !$payment_method || !$rider_name) {
    echo json_encode(["success"=>false,"message"=>"All fields are required."]);
    exit;
}

$transaction_id = '#' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));

$stmt = $conn->prepare(
    "INSERT INTO reservations
        (user_id, transaction_id, bike_name, ride_date, ride_time, amount, payment_method, rider_name)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param("issssdss",
    $user_id, $transaction_id, $bike_name,
    $ride_date, $ride_time, $amount,
    $payment_method, $rider_name
);

if ($stmt->execute()) {
    echo json_encode([
        "success"        => true,
        "transaction_id" => $transaction_id,
        "bike_name"      => $bike_name,
        "ride_date"      => $ride_date,
        "ride_time"      => $ride_time,
        "amount"         => $amount,
        "payment_method" => $payment_method,
        "rider_name"     => $rider_name,
    ]);
} else {
    echo json_encode(["success"=>false,"message"=>"Failed to save reservation."]);
}

$stmt->close();
$conn->close();
?>