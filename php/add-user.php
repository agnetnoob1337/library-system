<?php

require "./api-handler.php";
header('Content-Type: application/json');

$apiHandler = new ApiHandler();

$userInput = file_get_contents("php://input");
$mediaData = json_decode($userInput, true);

if((isset($mediaData["username"]) && !empty($mediaData["username"])) && (isset($mediaData["password"]) && !empty($mediaData["password"])) && (isset($mediaData["mail"]) && !empty($mediaData["mail"]))){
    
    $username = $mediaData["username"];
    $password = $mediaData["password"];
    $mail = $mediaData["mail"];
    $isAdmin = isset($mediaData["isAdmin"]) ? (bool)$mediaData["isAdmin"] : 0;

    echo $apiHandler->addUser($username, $password, $mail, $isAdmin);
} else {
    echo json_encode(["error" => "Missing required fields: username, password, mail"]);
}