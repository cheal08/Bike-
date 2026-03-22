<?php
require_once "db.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

header('Content-Type: application/json');

$email = $_POST['email'] ?? '';

if(!$email){
    echo json_encode(["success"=>false,"message"=>"Email required"]);
    exit;
}

$code = rand(100000,999999);

$stmt = $conn->prepare("INSERT INTO email_verifications(email,code) VALUES (?,?)");
$stmt->bind_param("ss",$email,$code);
$stmt->execute();
$mail = new PHPMailer(true);

try {

$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'whamos17@gmail.com';
$mail->Password = 'ffwl hozy gxoh btal';
$mail->SMTPSecure = 'tls';
$mail->Port = 587;

$mail->setFrom('whamos17@gmail.com','Ride A Pedal');
$mail->addAddress($email);

$mail->isHTML(true);
$mail->Subject = 'Email Verification Code';
$mail->Body = "<h2>Your Verification Code</h2><h1>$code</h1>";

$mail->send();

echo json_encode(["success"=>true]);

} catch (Exception $e) {
echo json_encode(["success"=>false,"message"=>$mail->ErrorInfo]);
}
?>