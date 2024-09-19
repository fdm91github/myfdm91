<?php
// Impostazioni di sicurezza per la sessione
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);

require_once 'config.php';
require 'vendor/autoload.php'; // Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    // Check if the email exists
    $sql = "SELECT id, email FROM users WHERE email = ?";
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            $stmt->store_result();
            if ($stmt->num_rows == 1) {
                // Generate a unique token
                $token = bin2hex(random_bytes(50));
                $stmt->bind_result($user_id, $user_email);
                $stmt->fetch();
                
                // Set expiration time (1 hour from now)
                $expires = date("U") + 3600;

                // Insert the token into the password_resets table
                $sql = "INSERT INTO password_resets (user_id, email, token, expires) VALUES (?, ?, ?, ?)";
                if ($stmt = $link->prepare($sql)) {
                    $stmt->bind_param("issi", $user_id, $email, $token, $expires);
                    $stmt->execute();
                }

                // Send a reset email using PHPMailer
                $reset_link = "https://my.fdm91.net/reset.php?token=$token&email=$email";
                $subject = "Richiesta di reimpostazione della password";
                $message = "Clicca su questo link per reimpostare la tua password: <a href='" . htmlspecialchars($reset_link) . "'>Reimposta Password</a>";

                // Create a PHPMailer instance
                $mail = new PHPMailer(true);

                try {
                    // SMTP server configuration
                    $mail->isSMTP();
                    $mail->Host = 'smtp.fdm91.net';  // SMTP server
                    $mail->Port = 25;  // SMTP port
                    $mail->SMTPAuth = false;  // No SMTP authentication
                    $mail->SMTPSecure = false;  // No SSL/TLS

                    // Sender info
                    $mail->setFrom('noreply@fdm91.net', 'Reset Password fdm91');

                    // Recipient
                    $mail->addAddress($email);  // User's email

                    // Email content
                    $mail->isHTML(true);  // Enable HTML format
                    $mail->Subject = $subject;
                    $mail->Body = $message;

                    // Send the email
                    $mail->send();
                    $success_message = "Le istruzioni per reimpostare la password sono state inviate alla tua email.";
                } catch (Exception $e) {
                    // Handle errors
                    $error_message = "Errore nell'invio dell'email: " . htmlspecialchars($mail->ErrorInfo);
                }
            } else {
                $error_message = "Non è stato trovato alcun account associato a questo indirizzo email.";
            }
        }
        $stmt->close();
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
    <style>
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            min-width: 350px;
			max-width: 500px;
            margin: auto;
        }
        .login-container {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
        }
    </style>
</head>
<body>
    <div class="container login-container">
        <div class="card">
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
