<?php
session_start();
require "./api-handler.php";
header('Content-Type: application/json');

$apiHandler = new ApiHandler();

if (isset($_SESSION["user_id"])) {
    $userId = $_SESSION["user_id"];
    echo $apiHandler->getUserLoanedMedia($userId);
} else {
    echo json_encode(["error" => "User not logged in"]);
}