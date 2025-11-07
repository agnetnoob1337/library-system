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
        <input type="search" id="search-input" placeholder="Sök media...">
        <div>
            <label for="media-type">Media typ:</label>
            <select name="media-type" id="media-type">
                <option value="">Alla typer</option>
                <option value="bok">Bok</option>
                <option value="ljudbok">Ljudbok</option>
                <option value="film">Film</option>
            </select>
        </div>
        <div>
            <button id="grid-button">Grid view</button>
            <button id="list-button">List view</button>
        </div>

    </menu>
    <main>
        <div id="media-catalog"class="media-grid">
            <div>
                <p>Test: &#128191;</p>
                <ul id="media-container">

                </ul>
            </div>
        </div>
    </main>


</body>
</html>