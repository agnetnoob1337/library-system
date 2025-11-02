<?php

require "./api-handler.php";
header('Content-Type: application/json');

$apiHandler = new ApiHandler();

$userinput = json_decode(file_get_contents('php://input'), true);

if(isset($userinput['mediaId']) && !empty($userinput['mediaId'])) {
    $mediaId = intval($userinput['mediaId']);
    $result = $apiHandler->removeCopy($mediaId);
    echo json_encode($result);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid media ID'
    ]);
}