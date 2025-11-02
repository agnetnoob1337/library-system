<?php
session_start();
require "./api-handler.php";
header('Content-Type: application/json');
$apiHandler = new ApiHandler();


$userInput = file_get_contents("php://input");
$returnData = json_decode($userInput, true);

if (isset($returnData["mediaIds"])) {
    $mediaIDs = $returnData["mediaIds"];
    $userId = $_SESSION["user_id"];

    echo $apiHandler->returnMedia($mediaIDs, $userId);
} else {
    echo json_encode(["error" => "Missing required fields: mediaIDs"]);
}