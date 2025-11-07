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
        <div class="search-bar">
            <input type="search" id="search-input" placeholder="Sök media..." />
            <button id="record-btn" class="record-btn" type="button" aria-label="Röstinspelning">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 14a3 3 0 0 0 3-3V5a3 3 0 0 0-6 0v6a3 3 0 0 0 3 3zm5-3a5 5 0 0 1-10 0H5a7 7 0 0 0 14 0h-2zM11 18v3h2v-3h-2z"/>
                </svg>
            </button>
        </div>

        <div class="filter-group">
            <label for="media-type">Media typ</label>
            <select name="media-type" id="media-type" class="filter-select">
                <option value="">Alla typer</option>
                <option value="bok">Bok</option>
                <option value="ljudbok">Ljudbok</option>
                <option value="film">Film</option>
            </select>
        </div>
        <div class="filter-group">
            <label for="search-for">Sök efter</label>
            <select name="media-type" id="search-for" class="filter-select">
                <option value="">Allt</option>
                <option value="title">Titel</option>
                <option value="category">Kategori</option>
                <option value="author">Författare/regissör</option>
            </select>
        </div>
        <div id="view-options-container" class="btn-group">
            <button id="grid-button" class="btn toggle-btn">🔳 Rutnät</button>
            <button id="list-button" class="btn toggle-btn">📄 Lista</button>
        </div>

        <div class="btn-group navigation-group">
            <button id="show-user-loans" class="btn nav-btn">📚 Dina lån</button>
            <button id="show-media" class="btn nav-btn">⬅️ Tillbaka</button>
        </div>
        <div class="user-menu">
            <button class="user-icon" id="user-menu-btn">👤</button>
            <div class="user-dropdown" id="user-dropdown">
                <form action="php/password-change.php" method="post" target="_blank">
                    <input type="hidden" name="userId" value="<?php echo $_SESSION['user_id'] ?>">
                    <button type="submit" class="dropdown-item">Ändra lösenord</button>
                </form>
                <a href="php/logout.php" class="dropdown-item logout">Logga ut</a>
            </div>
        </div>

    </menu>
    <main>
        <div id="media-catalog">
            <div>
                <ul id="media-container">

                </ul>
            </div>
        </div>
        <div id="media-borrowed-container">
            <div>
                <ul id="media-borrowed">

                </ul>
            </div>
        </div>
    </main>

    <script>
        let mediaRecorder;
        let audioChunks = [];
        let isRecording = false;

        const recordBtn = document.getElementById("record-btn");

        recordBtn.onclick = async () => {
            if(!isRecording){
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                mediaRecorder = new MediaRecorder(stream, { mimeType: 'audio/webm; codecs=opus' });
                audioChunks = [];

                mediaRecorder.ondataavailable = event => audioChunks.push(event.data);
                mediaRecorder.start();

                recordBtn.classList.add('recording');
                recordBtn.querySelector('svg').style.color = 'red';

                isRecording = true;
            }
            else {
                if (!mediaRecorder) return;

                mediaRecorder.stop();
                mediaRecorder.onstop = async () => {
                    const blob = new Blob(audioChunks, { type: 'audio/webm' });
                    const formData = new FormData();
                    formData.append('audio', blob, 'recording.webm');

                    try {
                        const response = await fetch('google-api-call.php', { method: 'POST', body: formData });
                        const result = await response.json();
                        document.getElementById('search-input').value = result.transcript;
                        const event = new Event('input', { bubbles: true });
                        document.getElementById('search-input').dispatchEvent(event);
                    } catch (e) {
                        console.error(e);
                        document.getElementById('search-input').value = 'Fel vid transkription';
                    }
                };
                recordBtn.classList.remove('recording');
                recordBtn.querySelector('svg').style.color = '';

                isRecording = false;
            }
        };

    </script>

</body>
</html>