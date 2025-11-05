<?php
// require "./api-handler.php";
// header('Content-Type: application/json');
// $apiHandler = new ApiHandler();

// $requiredFields = ['title', 'ISBN', 'filter'];
// $optinalFields = ['filter', 'availableOnly', 'SABCategory'];

// //check if one of the required fields is set
// $hasRequiredField = false;
// foreach ($requiredFields as $field) {
//     if (isset($_GET[$field]) && $_GET[$field] !== '') {
//         $hasRequiredField = true;
//         break;
//     }
// }

// if (!isset($_GET['availableOnly'])) {
//     $available = null; // not specified
// } elseif ($_GET['availableOnly'] === 'true' || $_GET['availableOnly'] === '1') {
//     $available = true;
// } elseif ($_GET['availableOnly'] === 'false' || $_GET['availableOnly'] === '0') {
//     $available = false;
// } else {
//     $available = null; // invalid value -> treat as unset
// }

// if ($hasRequiredField) {

//     $params = [
//         'title' => $_GET['title'] ?? "",
//         'ISBN' => $_GET['ISBN'] ?? "",
//         'filter' => $_GET['filter'] ?? "",
//         'searchFor' => $_GET['searchFor'] ?? "",
//         'searchTerm' => $_GET['searchTerm'] ?? "",
//         'SABCategory' => $_GET['SABCategory'] ?? "",
//         'id' => $_GET['id'] ?? "",
//     ];

//     if (!isset($_GET['availableOnly'])) {
//         $available = null; // not specified
//     } elseif ($_GET['availableOnly'] === 'true' || $_GET['availableOnly'] === '1') {
//         $available = true;
//     } elseif ($_GET['availableOnly'] === 'false' || $_GET['availableOnly'] === '0') {
//         $available = false;
//     } else {
//         $available = null; // invalid value -> treat as unset
//     }
//     echo $apiHandler->getMedia($params, $available);
// } else {
//     echo $apiHandler->getMedia(['id' => $_GET['id'] ?? ""], $available);
// }
require "./api-handler.php";
header('Content-Type: application/json');
$apiHandler = new ApiHandler();

// Hämta alla GET-parametrar
$params = [
    'title' => $_GET['title'] ?? "",
    'ISBN' => $_GET['ISBN'] ?? "",
    'filter' => $_GET['filter'] ?? "", 
    'searchFor' => $_GET['searchFor'] ?? "",
    'searchTerm' => $_GET['searchTerm'] ?? "",
    'SABCategory' => $_GET['SABCategory'] ?? "",
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

echo $apiHandler->getMedia($params, $available);