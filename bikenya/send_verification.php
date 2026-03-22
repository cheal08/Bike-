<?php
require_once 'db.php';         
header('Content-Type: application/json');

$full_name = trim($_POST['full_name'] ?? '');
$email     = trim($_POST['email']     ?? '');
$phone     = trim($_POST['phone']     ?? '');
$password  = $_POST['password']       ?? '';  

if (!$full_name || !$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'All required fields must be filled.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}
$chk = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$chk->bind_param('s', $email);
$chk->execute();
$chk->store_result();
if ($chk->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'This email is already registered.']);
    $chk->close(); $conn->close();
    exit;
}
$chk->close();
$code          = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$hashed_pass   = password_hash($password, PASSWORD_DEFAULT);
$expires_at    = date('Y-m-d H:i:s', time() + 600); 
$conn->query("DELETE FROM email_verifications WHERE email = '" . $conn->real_escape_string($email) . "'");

$ins = $conn->prepare(
    "INSERT INTO email_verifications
        (email, code, full_name, phone, hashed_password, expires_at)
     VALUES (?, ?, ?, ?, ?, ?)"
);
$ins->bind_param('ssssss', $email, $code, $full_name, $phone, $hashed_pass, $expires_at);

if (!$ins->execute()) {
    echo json_encode(['success' => false, 'message' => 'Database error. Please try again.']);
    $ins->close(); $conn->close();
    exit;
}
$ins->close();
$conn->close();
require_once 'PHPMailer-master/src/Exception.php';
require_once 'PHPMailer-master/src/PHPMailer.php';
require_once 'PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
define('SMTP_HOST',     'smtp.gmail.com');
define('SMTP_USER',     'whamos17@gmail.com');   
define('SMTP_PASS',     'ffwl hozy gxoh btal');    
define('SMTP_PORT',     587);
define('MAIL_FROM',     'whamos17@gmail.com');  
define('MAIL_FROM_NAME','Ride A Pedal');

$mail = new PHPMailer(true);

try {
    $mail->SMTPDebug  = SMTP::DEBUG_OFF;  
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = SMTP_PORT;
    $mail->CharSet    = 'UTF-8';
    $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
    $mail->addAddress($email, $full_name);
    $mail->isHTML(true);
    $mail->Subject = 'Your Ride A Pedal Verification Code';

    $mail->Body = '
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset="UTF-8">
      <style>
        body { font-family: Poppins, Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .wrapper { max-width: 480px; margin: 40px auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .header  { background: linear-gradient(135deg, #e6b85c, #8b4d1d); padding: 30px; text-align: center; }
        .header h1 { color: #fff; margin: 0; font-size: 24px; letter-spacing: 2px; }
        .body    { padding: 36px 40px; text-align: center; }
        .body p  { color: #444; font-size: 15px; margin-bottom: 20px; }
        .code-box { display: inline-block; background: #fef3d0; border: 2px dashed #e6a817; border-radius: 12px; padding: 18px 40px; font-size: 38px; font-weight: 700; letter-spacing: 10px; color: #8b4d1d; margin: 10px 0 24px; }
        .note    { color: #888; font-size: 13px; }
        .footer  { background: #f9f3e8; padding: 16px; text-align: center; color: #aaa; font-size: 12px; }
      </style>
    </head>
    <body>
      <div class="wrapper">
        <div class="header"><h1>🚲 Ride A Pedal</h1></div>
        <div class="body">
          <p>Hello, <strong>' . htmlspecialchars($full_name) . '</strong>!</p>
          <p>Use the code below to verify your email address and complete your registration.</p>
          <div class="code-box">' . $code . '</div>
          <p class="note">⏱ This code expires in <strong>10 minutes</strong>.<br>If you did not request this, you can safely ignore this email.</p>
        </div>
        <div class="footer">© ' . date('Y') . ' Ride A Pedal · Intramuros, Manila</div>
      </div>
    </body>
    </html>';

    $mail->AltBody = "Hello {$full_name},\n\nYour Ride A Pedal verification code is: {$code}\n\nThis code expires in 10 minutes.\n\nIf you did not request this, ignore this email.";

    $mail->send();
    echo json_encode(['success' => true, 'message' => 'Verification code sent to ' . $email]);

} catch (Exception $e) {
    error_log('PHPMailer Error [send_verification]: ' . $mail->ErrorInfo);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to send email. Check SMTP settings. (' . $mail->ErrorInfo . ')'
    ]);
}