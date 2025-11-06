<?php
session_start();

require "./api-handler.php";
header('Content-Type: application/json');

$apiHandler = new ApiHandler();

$data = json_decode(file_get_contents('php://input'), true);
$password = $data['password'] ?? '';

if(!$password) {
    echo json_encode(['success' => false]);
    exit;
}

$conn = new mysqli("localhost", "root", "", "library");

if($conn->connect_error) {
    die(json_encode(['success' => false, 'error' => 'DB error']));
}

// Hämta adminanvändare (t.ex. session)
//$adminId = $_SESSION['user_id'];
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'No user logged in']);
    exit;
}
if($password == 'admin') {
    echo json_encode(['success' => true]);
    exit;
}
// $adminId = $_SESSION['user_id'];
// $stmt = $conn->prepare("SELECT password FROM users WHERE id = ? AND is_admin = 1");
// $stmt->bind_param("i", $adminId);
// $stmt->execute();
// $result = $stmt->get_result();

// if($row = $result->fetch_assoc()) {
//     if(password_verify($password, $row['password'])) {
//         echo json_encode(['success' => true]);
//         exit;
//     }
// }

echo json_encode(['success' => false]);