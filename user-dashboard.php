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
    <script src="./js/user-dashboard.js" defer></script>
    <title>user dashboard</title>
</head>
<body>
    <menu>
        <button id="checkout">Låna</button>
        <button id="return">Lämna tillbaka</button>
        <input type="search" id="search-input" placeholder="Sök media...">
        <div>
            <label for="media-type">Media typ:</label>
            <select name="media-type" id="media-type">
                <option value="">Alla typer</option>
                <option value="book">Bok</option>
                <option value="audiobook">Ljudbok</option>
                <option value="film">Film</option>
            </select>
        </div>

    </menu>
    <main>
        <table>
            
            <thead>
                <tr>
                    <th></th>
                    <th>Titel</th>
                    <th>Författare/Regissör</th>
                    <th>ISBN</th>
                    <th>IMDB ID</th>
                    <th>SAB Kategori</th>
                    <th>Media typ</th>
                </tr>
            </thead>
            <tbody id="available-media-table-body">
            </tbody>
        </table>

        <table>
            <h3>Dina lån</h3>
            <thead>
                <tr>
                    <th></th>
                    <th>Titel</th>
                    <th>Författare/Regissör</th>
                    <th>ISBN</th>
                    <th>SAB Kategori</th>
                    <th>Media typ</th>
                    <th>Återlämningsdatum</th>
                </tr>
            </thead>
            <tbody id="borrowed-media-table-body">
            </tbody>
        </table>

        <table>
            <h3>Försenade</h3>
            <thead>
                <tr>
                    <th>Titel</th>
                    <th>Återlämningsdatum</th>
                    <th>Avgift</th>
                </tr>
            </thead>
            <tbody id="late-returns-media-table-body">
            </tbody>
        </table>
    </main>
</body>
</html>