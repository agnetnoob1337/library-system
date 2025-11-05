<?php
session_start();
require "./api-handler.php";
header('Content-Type: application/json');
$apiHandler = new ApiHandler();

$userInput = file_get_contents("php://input");
$returnData = json_decode($userInput, true);

// fallback: om caller skickade via querystring, plocka också det
if (!$returnData) $returnData = [];

$mediaId = isset($returnData['mediaId']) ? (int)$returnData['mediaId'] : (int)($_GET['mediaId'] ?? 0);
$copyId  = isset($returnData['copyId'])  ? (int)$returnData['copyId']  : (int)($_GET['copyId'] ?? 0);
$userId  = isset($returnData['userId'])  ? (int)$returnData['userId']  : (int)($_GET['userId'] ?? 0);
if($_SESSION['user_id'] != 0){
    $userId  = $_SESSION['user_id'] ?? null;
}

if ($userId === null) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

if ($mediaId > 0 && $copyId > 0) {
    echo $apiHandler->returnMedia($mediaId, $userId, $copyId);
} else {
    echo json_encode(['success' => false, 'message' => 'Missing mediaId or copyId']);
}
