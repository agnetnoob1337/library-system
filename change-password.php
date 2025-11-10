<?php
session_start();

if(!isset($_GET['token'])){
    header("Location: index.html");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ändra lösenord</title>
    <link rel="stylesheet" href="./css/index.css">
</head>
<body>
    <form class="login-box" action="./php/change-password.php" method="POST">
        <h2>Ändra lösenord</h2>
    
        <label for="passwordNew">Nytt lösenord</label>
        <input type="password" class="passwordNew" name="passwordNew" required>
            
        <label for="passwordNew">Bekräfta nytt lösenord</label>
        <input type="password" class="passwordNewConfirm" name="passwordNewConfirm" required>

        <input type="hidden" name="token" id="" value="<?php echo $_GET['token'] ?>">
        
        <button type="submit">Ändra lösenord</button>
      </form>
</body>
</html>