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
    <a href="php/logout.php">Logga ut</a>
    <form action="php/password-change.php" method="post" target="_blank">
        <input type="hidden" name="userId" value="<?php echo $_SESSION['user_id'] ?>">
        <button type="submit">Ändra lösenord</button>
    </form>
    <menu>
        <div class="search-bar">
            <input type="search" id="search-input" placeholder="Sök media..." />
            <button id="record-btn" class="record-btn" type="button" aria-label="Röstinspelning">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 14a3 3 0 0 0 3-3V5a3 3 0 0 0-6 0v6a3 3 0 0 0 3 3zm5-3a5 5 0 0 1-10 0H5a7 7 0 0 0 14 0h-2zM11 18v3h2v-3h-2z"/>
                </svg>
            </button>
        </div>

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
            <label for="media-type">Sök efter:</label>
            <select name="media-type" id="search-for">
                <option value="">Allt</option>
                <option value="title">Titel</option>
                <option value="category">Kategori</option>
                <option value="author">Författare/regissör</option>
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