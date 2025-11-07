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

// get the audio file
$audioFile = $_FILES['audio']['tmp_name'];
// base64 encode the audio
$audioData = base64_encode(file_get_contents($audioFile));

// get the api key and the url
$apiKey = $inifile['API_KEY'];
$url = "https://speech.googleapis.com/v1/speech:recognize?key=$apiKey";

// configure for webM/opus
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

// poat request to the google api
$options = [
    'http' => [
        'header'  => "Content-type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($requestBody)
    ]
];

// creates a stream context and makes the api call and gets the result
$context  = stream_context_create($options);
$response = file_get_contents($url, false, $context);

if ($response === FALSE) {
    echo json_encode(['transcript' => 'Fel vid API-anrop']);
    exit;
}

// get the result
$result = json_decode($response, true);
$transcript = '';

if (isset($result['results'])) {
    foreach ($result['results'] as $r) {
        // get the result into a transcript
        $transcript .= $r['alternatives'][0]['transcript'] . ' ';
    }
    $transcript = trim($transcript);
} else {
    $transcript = "Ingen transkription hittades.";
}

// echo the result
echo json_encode(['transcript' => $transcript]);

?>