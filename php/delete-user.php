<?php

require "./api-handler.php";
header('Content-Type: application/json');

$apiHandler = new ApiHandler();

$userInput = file_get_contents("php://input");
$userData = json_decode($userInput, true);

if (isset($userData["userId"]) && !empty($userData["userId"])) {
    $userId = $userData["userId"];

    echo $apiHandler->removeUser($userId);
} else {
    echo json_encode(["error" => "Missing required field: username"]);
}