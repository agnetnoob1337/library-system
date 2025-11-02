<?php

require "./api-handler.php";
header('Content-Type: application/json');

$apiHandler = new ApiHandler();

$userInput = file_get_contents("php://input");
$mediaData = json_decode($userInput, true);

if (isset($mediaData['id']) && !empty($mediaData['id'])) {
    $params = [
        'id' => intval($mediaData['id']),
        'title' => $mediaData['title'] ?? null,
        'author' => $mediaData['author'] ?? null,
        'SABSignum' => $mediaData['SABSignum'] ?? null,
        'price' => isset($mediaData['price']) ? intval($mediaData['price']) : null,
        'book' => isset($mediaData['book']) ? (bool)$mediaData['book'] : null,
        'audioBook' => isset($mediaData['audioBook']) ? (bool)$mediaData['audioBook'] : null,
        'film' => isset($mediaData['film']) ? (bool)$mediaData['film'] : null,
        'ISBN' => $mediaData['ISBN'] ?? null,
        'IMDB' => $mediaData['IMDB'] ?? null,
    ];
    echo $apiHandler->editMedia($params);
} else {
    echo json_encode(["error" => "Missing required field: id"]);
}