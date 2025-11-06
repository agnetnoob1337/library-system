<?php
$dbServer = "localhost";
$dbUser = "root";
$dbPass = "";
$dbName = "library";

$conn = new mysqli($dbServer, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$response = ['status' => 'available'];

if (isset($_POST['username'])) {
    $username = trim($_POST['username']);
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $response['status'] = 'exists';
        $response['field'] = 'username';
    }
} elseif (isset($_POST['email'])) {
    $email = trim($_POST['email']);
    $stmt = $conn->prepare("SELECT id FROM users WHERE mail = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $response['status'] = 'exists';
        $response['field'] = 'email';
    }
}

header('Content-Type: application/json');
echo json_encode($response);
$conn->close();
?>
