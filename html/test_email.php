<?php
require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function makeMailer(array $config): PHPMailer {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = $config['smtp']['host'];
    $mail->Port       = $config['smtp']['port'];
    $mail->SMTPAuth   = $config['smtp']['auth'];
    $mail->Username   = $config['smtp']['username'] ?? null;
    $mail->Password   = $config['smtp']['password'] ?? null;
    $mail->SMTPAutoTLS = $config['smtp']['autoTLS'] ?? true;

    // STARTTLS su 587 oppure SMTPS su 465
    if (($config['smtp']['secure'] ?? 'tls') === 'ssl' || (int)$config['smtp']['port'] === 465) {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // 465
    } else {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // 587
    }

    if (!empty($config['smtp']['options'])) {
        $mail->SMTPOptions = $config['smtp']['options'];
    }

    $mail->CharSet = 'UTF-8';
    $mail->setFrom($config['email']['from_address'], $config['email']['from_name']);
    if (!empty($config['email']['reply_to'])) {
        $mail->addReplyTo($config['email']['reply_to']);
    }
    return $mail;
}

// Test invio
try {
    $mail = makeMailer($config);
    $mail->addAddress('francesco.dm91@gmail.com');
    $mail->Subject = 'Test SMTP 587 STARTTLS';
    $mail->isHTML(true);
    $mail->Body = '<p>Invio da MyFDM91 inviato con successo!</p>';
    $mail->AltBody = 'OK da MyFDM91';
    $mail->send();
    echo "Inviata.\n";
} catch (Exception $e) {
    echo "Errore invio: {$e->getMessage()}\n";
}

