<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';
require "./api-handler.php";
$apiHandler = new ApiHandler();

$mail = new PHPMailer(true);

$token = $apiHandler->generateToken();

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'elias.moll38@gmail.com';
    $mail->Password   = 'zwou xada ukbw aohv';
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('elias.moll38@gmail.com', 'Bibliotekarie Stina');
    $mail->addAddress($_SESSION['user_mail'], $_SESSION['username']);

    $mail->isHTML(true);
    $mail->Subject = 'Change password';
    $mail->Body    = '<html>
        <body>
            <p>Hej '.$_SESSION['username'].'!. <br> Vänligen klicka på länken nedan för att ändra ditt lösenord:</p>
            <a href="http://localhost:8080/library-system/change-password.php?token='.$token.'" target="_blank">Ändra lösenord</a>
        </body>
        </html>';

    $mail->send();
    header("Location: ../media-display-page.php");
} catch (Exception $e) {
    echo "Mail kunde inte skickas. Fel: {$mail->ErrorInfo}";
}
?>
