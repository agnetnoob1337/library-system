<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ./index.html');
    exit;
}

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="./css/user-dashboard.css">

    <link rel="stylesheet" href="./css/catalog-media.css">

    <script src="./js/catalog-media.js" defer></script>
    <title>user dashboard</title>
</head>
<body>
    <menu>

    </menu>
    <main>
        <div class="media-grid">
            <div>
                <p>Test: &#128191;</p>
                <ul id="media-container">

                </ul>
            </div>
        </div>
    </main>


</body>
</html>