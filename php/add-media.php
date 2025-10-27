<?php

require "./api-handler.php";

$apiHandler = new ApiHandler();

$userInput = file_get_contents("php://input");
$mediaData = json_decode($userInput, true);
/*
if (isset($mediaData['title'], $mediaData['SABSignum'], $mediaData['ISBN'])) {
    $title = $mediaData['title'];
    $author = $mediaData['author'] ?? "";
    $SABSignum = $mediaData['SABSignum'];
    $price = $mediaData['price'] ?? 0;
    $book = isset($mediaData['book']) ? (bool)$mediaData['book'] : false;
    $audioBook = isset($mediaData['audioBook']) ? (bool)$mediaData['audioBook'] : false;
    $film = isset($mediaData['film']) ? (bool)$mediaData['film'] : false;
    $ISBN = $mediaData['ISBN'];

    //$apiHandler->addMedia($title, $author, $SABSignum, $price, $book, $audioBook, $film, $ISBN);
} else {
    echo "Missing required fields: title, SABSignum, or ISBN.";
}*/

$apiHandler->addMedia("Sample Title", "Sample Author", "SAB123", 19.99, true, false, false, "1234567890123");