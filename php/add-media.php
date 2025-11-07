<?php

require "./api-handler.php";
header('Content-Type: application/json');

$apiHandler = new ApiHandler();

$userInput = file_get_contents("php://input");
$mediaData = json_decode($userInput, true);
error_log("Raw input: " . $userInput);
error_log("Decoded input: " . print_r($mediaData, true));

if (isset($mediaData['title'], $mediaData['SABSignum'], $mediaData['price'], $mediaData['author']) && (isset($mediaData['ISBN']) || isset($mediaData['IMDB']))) {
    $title = $mediaData['title'];
    $author = $mediaData['author'] ?? "";
    $SABSignum = $mediaData['SABSignum'];
    $price = intval($mediaData['price']) ?? 0;
    //$book = isset($mediaData['book']) ? (bool)$mediaData['book'] : false;
    //$audioBook = isset($mediaData['audioBook']) ? (bool)$mediaData['audioBook'] : false;
    //$film = isset($mediaData['film']) ? (bool)$mediaData['film'] : false;
    $mediaType = $mediaData['mediaType'];
    $ISBN = $mediaData['ISBN'];
    $quantity = $mediaData['quantity'] ?? 1;
    $IMDB = $mediaData['IMDB'] ?? "";

    echo $apiHandler->addMedia($title, $author, $SABSignum, $price, $mediaType, $ISBN, $quantity, $IMDB);
} else {
    echo "Missing required fields: title, SABSignum, or ISBN.";
}