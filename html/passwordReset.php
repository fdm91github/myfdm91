<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);

require_once 'config.php';        // deve definire $link e $config
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    // (opz.) Validazione email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Formato email non valido.";
    } else {
        // 1) Cerca l'utente
        $sql = "SELECT id, email FROM users WHERE email = ? LIMIT 1";
        if ($stmtUser = $link->prepare($sql)) {
            $stmtUser->bind_param("s", $email);
            if ($stmtUser->execute()) {
                $stmtUser->store_result();

                // Messaggio "generico" per non rivelare l'esistenza dell'account
                $generic_ok = "Se l'email è registrata, riceverai le istruzioni per reimpostare la password.";

                if ($stmtUser->num_rows == 1) {
                    $stmtUser->bind_result($user_id, $user_email);
                    $stmtUser->fetch();

                    // (opz.) mantieni un solo token per utente
                    if ($stmtDel = $link->prepare("DELETE FROM password_resets WHERE user_id = ?")) {
                        $stmtDel->bind_param("i", $user_id);
                        $stmtDel->execute();
                        $stmtDel->close();
                    }

                    // 2) Crea token e scadenza
                    $token   = bin2hex(random_bytes(32)); // 64 char hex
                    $expires = time() + 3600;             // +1h (INT)

                    // 3) Salva il token
                    $sqlIns = "INSERT INTO password_resets (user_id, email, token, expires) VALUES (?, ?, ?, ?)";
                    if ($stmtIns = $link->prepare($sqlIns)) {
                        $stmtIns->bind_param("issi", $user_id, $email, $token, $expires);
                        $stmtIns->execute();
                        $stmtIns->close();
                    } else {
                        $error_message = "Errore interno: impossibile preparare l'inserimento token.";
                        $stmtUser->close();
                        $link->close();
                        exit;
                    }

                    // 4) Costruisci link con URL-encode
                    $reset_link = rtrim($config['host_base'], '/')
                                . "/reset.php?token=" . rawurlencode($token)
                                . "&email=" . rawurlencode($email);

                    $subject = "Richiesta di reimpostazione della password";
                    $message = "
                        <p>Clicca sul seguente link per reimpostare la tua password:</p>
                        <p><a href='" . htmlspecialchars($reset_link, ENT_QUOTES, 'UTF-8') . "'>Reimposta Password</a></p>
                        <p>Se non hai richiesto questo reset, ignora questa mail.</p>
                        <p>Non vedi il pulsante? Copia e incolla nel browser questo link:</p>
                        <p><a href='" . htmlspecialchars($reset_link, ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($reset_link, ENT_QUOTES, 'UTF-8') . "</a></p>
                    ";

                    // 5) Invio email con PHPMailer (TLS su 587)
                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host       = $config['smtp']['host'];
                        $mail->Port       = $config['smtp']['port'];
                        $mail->SMTPAuth   = $config['smtp']['auth'];
                        $mail->Username   = $config['smtp']['username'] ?? null;
                        $mail->Password   = $config['smtp']['password'] ?? null;
                        $mail->SMTPAutoTLS = $config['smtp']['autoTLS'] ?? true;
                        // Forza STARTTLS su 587 (se usi 465 imposta ENCRYPTION_SMTPS)
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

                        if (!empty($config['smtp']['options'])) {
                            $mail->SMTPOptions = $config['smtp']['options'];
                        }

                        $mail->CharSet = 'UTF-8';
                        $mail->setFrom($config['email']['from_address'], $config['email']['from_name']);
                        $mail->addAddress($email);

                        $mail->isHTML(true);
                        $mail->Subject = $subject;
                        $mail->Body    = $message;
                        $mail->AltBody = "Apri questo link per reimpostare la password: " . $reset_link;

                        $mail->send();
                        // Messaggio "generico" per privacy
                        $success_message = $generic_ok;
                    } catch (Exception $e) {
                        // Non rivelare troppo al client, logga server-side
                        error_log("Errore PHPMailer: " . $mail->ErrorInfo);
                        // Messaggio generico anche in caso d'errore per non rivelare info
                        $success_message = $generic_ok;
                    }
                } else {
                    // Anche se non esiste: messaggio generico
                    $success_message = $generic_ok;
                }
            }
            $stmtUser->close();
        }
    }
    $link->close();
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reimposta Password</title>
    <?php include 'script.php' ?>
</head>
<body>
    <div class="container reset-container">
        <div class="card reset-card">
            <div class="card-header text-center bg-primary text-white">
                <h4>Reimposta la tua password</h4>
            </div>
            <div class="card-body">
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($error_message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>
                    </div>
                <?php endif; ?>
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($success_message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>
                    </div>
                <?php endif; ?>
                <form action="passwordReset.php" method="post">
                    <div class="mb-3">
                        <label for="email" class="form-label">Inserisci la tua email</label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="Indirizzo email" required>
                    </div>
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary w-100">Invia il link di reimpostazione</button>
                    </div>
                    <p class="text-center">
                        <a href="login.php" class="text-decoration-none">Torna al login</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

