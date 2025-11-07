<?php

require "./api-handler.php";
header('Content-Type: application/json');

$apiHandler = new ApiHandler();

$userinput = json_decode(file_get_contents('php://input'), true);

if(isset($userinput['copyId']) && !empty($userinput['copyId'])) {
    $copyId = intval($userinput['copyId']);
    $result = $apiHandler->removeSingularCopy($copyId);
    echo json_encode($result);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid copy ID'
    ]);
}