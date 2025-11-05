<?php
session_start();
$dbServer = "localhost";
$dbUser = "root";
$dbPass = "";
$dbName = "library";

$conn = new mysqli($dbServer, $dbUser, $dbPass, $dbName);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if(isset($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $conn->prepare("SELECT id, username FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    if($row = $result->fetch_assoc()) {
        $userId = $row['id'];
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'];
    } else {
        die("Ogiltig länk.");
    }
}
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['passwordNew'], $_POST['passwordNewConfirm'])) {
    $passwordNew = $_POST['passwordNew'];
    $passwordConfirm = $_POST['passwordNewConfirm'];

    if($passwordNew !== $passwordConfirm) {
        die("Passwords did not match.");
    }

    $passwordHashed = password_hash($passwordNew, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL WHERE id = ?");
    $stmt->bind_param("si", $passwordHashed, $_SESSION['user_id']);
    $stmt->execute();

    header('Location: ../user-dashboard.php');
    echo json_encode(['success' => 'Password change successful.']);
    exit;
}