<?php
require "./api-handler.php";
header('Content-Type: application/json');
$apiHandler = new ApiHandler();

// Hämta alla GET-parametrar
$params = [
    'filter' => $_GET['filter'] ?? "",
    'id' => $_GET['id'] ?? "",
];

// availableOnly
if (!isset($_GET['availableOnly'])) {
    $available = null;
} elseif ($_GET['availableOnly'] === 'true' || $_GET['availableOnly'] === '1') {
    $available = true;
} elseif ($_GET['availableOnly'] === 'false' || $_GET['availableOnly'] === '0') {
    $available = false;
} else {
    $available = null;
}

// Anropa alltid getMedia(), låt den hantera tomma filter
echo $apiHandler->getCopiesOfMedia($params, $available);