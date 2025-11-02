<?php

require "./api-handler.php";
header('Content-Type: application/json');

$apiHandler = new ApiHandler();

$userInput = file_get_contents("php://input");
$mediaData = json_decode($userInput, true);

if((isset($mediaData["username"]) && !empty($mediaData["username"])) && (isset($mediaData["password"]) && !empty($mediaData["password"]))){
    
    $username = $mediaData["username"];
    $password = $mediaData["password"];
    $isAdmin = isset($mediaData["isAdmin"]) ? (bool)$mediaData["isAdmin"] : 0;

    echo $apiHandler->addUser($username, $password, $isAdmin);
} else {
    echo json_encode(["error" => "Missing required fields: username, password"]);
}