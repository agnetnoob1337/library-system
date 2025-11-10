<?php
session_start();
require "./api-handler.php";
header('Content-Type: application/json');

$apiHandler = new ApiHandler();

if (isset($_SESSION["user_id"])) {
    $userId = $_SESSION["user_id"];
    $params = [
        'filter' => $_GET['filter'] ?? "", 
        'searchFor' => $_GET['searchFor'] ?? "",
        'searchTerm' => $_GET['searchTerm'] ?? "",
    ];
    echo $apiHandler->getUserLoanedMedia($params, $userId);
} else {
    echo json_encode(["error" => "User not logged in"]);
}