<?php

require "./api-handler.php";
header('Content-Type: application/json');

$apiHandler = new ApiHandler();

$userInput = file_get_contents("php://input");
$mediaData = json_decode($userInput, true);
error_log("Raw input: " . $userInput);
error_log("Decoded input: " . print_r($mediaData, true));

$mediaId = $mediaData["mediaId"];
$quantityCopy = $mediaData["quantityCopy"] ?? 1;

echo $apiHandler->addCopy($mediaId, $quantityCopy);