<?php
session_start();
require "./api-handler.php";
header('Content-Type: application/json');

$apiHandler = new ApiHandler();
$userInput = json_decode(file_get_contents("php://input"), true);

if(!isset($userInput['mail']) || empty($userInput['mail'])){
    echo json_encode(['status' => 'error', 'message' => 'Mail saknas']);
    exit;
}

$mail = $userInput['mail'];
$username = $apiHandler->getUsername($mail);
$userId = $apiHandler->getUserId($mail);

if($username){
    $_SESSION['user_mail'] = $mail;
    $_SESSION['username'] = $username;
    $_SESSION['user_id'] = $userId;
    echo json_encode(['status' => 'ok', 'username' => $username]);
}else{
    echo json_encode(['status' => 'error', 'message' => 'Mail finns inte']);
}

?>
