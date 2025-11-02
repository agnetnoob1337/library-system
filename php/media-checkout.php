<?php
session_start();

require "./api-handler.php";
header('Content-Type: application/json');
$apiHandler = new ApiHandler();

$userInput = file_get_contents("php://input");
$checkoutData = json_decode($userInput, true);

if (isset($checkoutData["mediaId"])) {
    $userId = $_SESSION["user_id"];
    $mediaID = $checkoutData["mediaId"];

    echo $apiHandler->checkoutMedia($userId, $mediaID);
} else {
    echo json_encode(["error" => "Missing required fields: username, mediaID"]);
}