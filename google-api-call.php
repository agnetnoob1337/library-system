<?php

$inifile = parse_ini_file(__DIR__ . "/secrets.ini");

if (!$inifile) {
    echo json_encode(['transcript' => 'FEL: Kan inte läsa secrets.ini']);
    exit;
}

header('Content-Type: application/json');

if (!isset($_FILES['audio'])) {
    echo json_encode(['transcript' => 'Ingen fil mottagen.']);
    exit;
}

// Läs filen och base64-encode
$audioFile = $_FILES['audio']['tmp_name'];
$audioData = base64_encode(file_get_contents($audioFile));

// Google API-nyckel
$apiKey = 'AIzaSyCD6yi14XWauUdNsU1MRICOaWM1Ra5C0nM';
$url = "https://speech.googleapis.com/v1/speech:recognize?key=$apiKey";

// Konfigurera för WebM/Opus (matchar MediaRecorder)
$requestBody = [
    'config' => [
        'encoding' => 'WEBM_OPUS',
        'sampleRateHertz' => 48000,
        'languageCode' => 'sv-SE'
    ],
    'audio' => [
        'content' => $audioData
    ]
];

// POST request till Google API
$options = [
    'http' => [
        'header'  => "Content-type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($requestBody)
    ]
];

$context  = stream_context_create($options);
$response = file_get_contents($url, false, $context);

if ($response === FALSE) {
    echo json_encode(['transcript' => 'Fel vid API-anrop']);
    exit;
}

// Hämta transkription
$result = json_decode($response, true);
$transcript = '';

if (isset($result['results'])) {
    foreach ($result['results'] as $r) {
        $transcript .= $r['alternatives'][0]['transcript'] . ' ';
    }
    $transcript = trim($transcript);
} else {
    $transcript = "Ingen transkription hittades.";
}

echo json_encode(['transcript' => $transcript]);

?>