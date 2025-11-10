<?php

require "./api-handler.php";
header('Content-Type: application/json');

$apiHandler = new ApiHandler();

$userInput = file_get_contents("php://input");
$mediaData = json_decode($userInput, true);

$requiredFields = ['username', 'password', 'mail'];

$hasRequiredField = false;
foreach ($requiredFields as $field) {
    if (isset($mediaData[$field]) && $mediaData[$field] !== '') {
        $hasRequiredField = true;
        break;
    }
}

if ($hasRequiredField && isset($mediaData['userId']) && !empty($mediaData['userId'])) {
    $params = [
        'username' => $mediaData['username'] ?? "",
        'password' => $mediaData['password'] ?? "",
        'isAdmin' => $mediaData['isAdmin'] ?? 0,
        'mail' => $mediaData['mail'] ?? "",
        'id' => $mediaData['userId'] ?? "",
    ];

    echo $apiHandler->editUser($params);
} else {
    echo json_encode(["error" => "Missing required fields."]);
}