<?php
session_start();
$conn = new mysqli("localhost", "root", "", "library");

$token = $_GET['token'] ?? $_POST['token'] ?? null;

if ($token) {
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'];
    } else {
        die("Ogiltig länk.");
    }
} else {
    die("Ingen token mottagen.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $passwordNew = $_POST['passwordNew'];
    $passwordConfirm = $_POST['passwordNewConfirm'];

    if ($passwordNew !== $passwordConfirm) {
        die("Passwords did not match.");
    }

    $passwordHashed = password_hash($passwordNew, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL WHERE id = ?");
    $stmt->bind_param("si", $passwordHashed, $_SESSION['user_id']);
    $stmt->execute();

    header("Location: ../index.html");
    echo "Password change successful.";
    exit;
}
