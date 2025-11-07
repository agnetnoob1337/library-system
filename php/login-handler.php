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


//register
if (isset($_POST['mail'])) {
    // ----------- REGISTER ACCOUNT -----------
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $email = trim($_POST['mail']);

    if (empty($username) || empty($password) || empty($email)) {
        header("Location: ../index.html");
        exit();
    }

    // Check if username or email already exists
    $check = $conn->prepare("SELECT * FROM users WHERE username = ? OR mail = ?");
    $check->bind_param("ss", $username, $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        header("Location: ../index.html");
        exit();
    }

    // Hash password before saving
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (username, password, mail, is_admin) VALUES (?, ?, ?, 0)");
    $stmt->bind_param("sss", $username, $hashedPassword, $email);

    if ($stmt->execute()) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['user_mail'] = $email;
        $_SESSION['is_admin'] = 0;

        header("Location: ../user-dashboard.php?");
        exit();
    } else {
        header("Location: ../index.html");
        exit();
    }
}else{  
    //login
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
                    header('Location: ../media-display-page.php');
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
}