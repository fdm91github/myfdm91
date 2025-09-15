<?php
require_once '../config.php';
require '../vendor/autoload.php'; // PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
$email = isset($data['email']) ? trim($data['email']) : '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Formato email non valido.']);
    exit;
}

// 1) L’utente esiste?
$sqlUser = "SELECT id FROM users WHERE email = ? LIMIT 1";
if (!$stmtUser = $link->prepare($sqlUser)) {
    echo json_encode(['success' => false, 'message' => 'Errore interno (prepare utente).']);
    exit;
}
$stmtUser->bind_param("s", $email);
$stmtUser->execute();
$stmtUser->store_result();

if ($stmtUser->num_rows !== 1) {
    $stmtUser->close();
    echo json_encode(['success' => false, 'message' => 'Email non trovata.']);
    exit;
}

$stmtUser->bind_result($user_id);
$stmtUser->fetch();
$stmtUser->close();

// 2) Genera token e scadenza (+1h)
$token   = bin2hex(random_bytes(32)); // 64 hex
$expires = time() + 3600;             // INT epoch

// 3) Salva/aggiorna token
$sqlIns = "INSERT INTO email_verifications (user_id, email, token, expires)
           VALUES (?, ?, ?, ?)
           ON DUPLICATE KEY UPDATE token = VALUES(token), expires = VALUES(expires)";
if (!$stmtIns = $link->prepare($sqlIns)) {
    echo json_encode(['success' => false, 'message' => 'Errore interno (prepare token).']);
    exit;
}
$stmtIns->bind_param("issi", $user_id, $email, $token, $expires);
if (!$stmtIns->execute()) {
    $stmtIns->close();
    echo json_encode(['success' => false, 'message' => 'Errore durante il salvataggio del token.']);
    exit;
}
$stmtIns->close();

// 4) Link di verifica (URL-encode del token)
$verification_link = rtrim($config['host_base'], '/')
                   . "/mysettings/verify_email.php?token=" . rawurlencode($token);

// 5) Componi email
$subject = "Verifica la tua email";
$linkEsc = htmlspecialchars($verification_link, ENT_QUOTES, 'UTF-8');
$message = "<p>Clicca su questo link per verificare il tuo indirizzo email:</p>
            <p><a href='{$linkEsc}'>Verifica Email</a></p>
            <p>Se il pulsante non funziona, copia e incolla questo URL nel browser:</p>
            <p><a href='{$linkEsc}'>{$linkEsc}</a></p>";
$altBody = "Apri questo link per verificare la tua email: {$verification_link}";

// 6) Invio con PHPMailer usando config.php
try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = $config['smtp']['host'] ?? 'localhost';
    $mail->Port       = (int)($config['smtp']['port'] ?? 587);
    $mail->SMTPAuth   = (bool)($config['smtp']['auth'] ?? true);
    $mail->Username   = $config['smtp']['username'] ?? null;
    $mail->Password   = $config['smtp']['password'] ?? null;

    // STARTTLS (587) vs SMTPS (465)
    $secure = strtolower((string)($config['smtp']['secure'] ?? 'tls'));
    if ($secure === 'ssl' || $mail->Port === 465) {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    } else {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    }
    $mail->SMTPAutoTLS = (bool)($config['smtp']['autoTLS'] ?? true);
    if (!empty($config['smtp']['options'])) {
        $mail->SMTPOptions = $config['smtp']['options'];
    }

    $mail->CharSet = 'UTF-8';
    $mail->setFrom($config['email']['from_address'], $config['email']['from_name']);
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $message;
    $mail->AltBody = $altBody;

    $mail->send();
    echo json_encode(['success' => true, 'message' => 'Email di verifica inviata con successo!']);
} catch (Exception $e) {
    // Log tecnico server-side, risposta generica client
    error_log('PHPMailer error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Errore nell\'invio dell\'email.']);
}

$link->close();
