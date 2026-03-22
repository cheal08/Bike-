<?php
require_once "db.php";

header('Content-Type: application/json');

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

$stmt = $conn->prepare(
"INSERT INTO users(name,email,password) VALUES(?,?,?)"
);

$stmt->bind_param("sss",$name,$email,$password);

if($stmt->execute()){
    echo json_encode(["success"=>true]);
}else{
    echo json_encode(["success"=>false]);
}
?>