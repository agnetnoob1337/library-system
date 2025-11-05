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

if((isset($_POST['username']) && !empty($_POST['username'])) && (isset($_POST['password'])) && !empty($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
}
if($username != "admin"){
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['is_admin'] = $row['is_admin'];
            $_SESSION['user_mail'] = $row['mail'];
            $_SESSION['username'] = $username;

            if($row['is_admin'] == 1){
                header('Location: ../admin-dashboard.php');
                echo json_encode(['success' => 'Login successful.']);
            }
            else{
                header('Location: ../user-dashboard.php');
                echo json_encode(['success' => 'Login successful.']);
            }
        } else {
            header('Location: ../index.html');
            echo json_encode(['error' => 'Invalid username or password.']);
        }
    } else {
        echo json_encode(['error' => 'Invalid username or password.']);
        header('Location: ../index.html');
    }
}else{
    if($password === "admin"){
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = 0;
        $_SESSION['is_admin'] = 1;
        echo json_encode(['success' => 'Admin login successful.']);
        header('Location: ../admin-dashboard.php');
    }else{
        echo json_encode(['error' => 'Invalid admin password.']);
        header('Location: ../index.html');
    }

}