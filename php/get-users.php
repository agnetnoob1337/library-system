<?php

require "./api-handler.php";
header('Content-Type: application/json');
$apiHandler = new ApiHandler();

if(isset($_GET['userId']) && !empty($_GET['userId'])) {
    $userId = intval($_GET['userId']);
    echo $apiHandler->getUsers($userId);

}
else {
    echo $apiHandler->getUsers();
}
