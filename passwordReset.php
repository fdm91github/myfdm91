<?php
require_once 'config.php';
require 'vendor/autoload.php'; // Aggiungi il caricamento di PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    // Controllo se l'email esiste
    $sql = "SELECT id, email FROM users WHERE email = ?";
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            $stmt->store_result();
            if ($stmt->num_rows == 1) {
                // Genero un token univoco
                $token = bin2hex(random_bytes(50));
                $stmt->bind_result($user_id, $user_email);
                $stmt->fetch();
                
                // Imposto il tempo di scadenza (1 ora da ora)
                $expires = date("U") + 3600;

                // Inserisco il token nella tabella password_resets
                $sql = "INSERT INTO password_resets (user_id, email, token, expires) VALUES (?, ?, ?, ?)";
                if ($stmt = $link->prepare($sql)) {
                    $stmt->bind_param("issi", $user_id, $email, $token, $expires);
                    $stmt->execute();
                }

                // Invio un'email di reset usando PHPMailer
                $reset_link = "https://my.fdm91.net/reset.php?token=$token&email=$email";
                $subject = "Richiesta di reimpostazione della password";
                $message = "Clicca su questo link per reimpostare la tua password: <a href='" . $reset_link . "'>Reimposta Password</a>";

                // Creazione dell'istanza di PHPMailer
                $mail = new PHPMailer(true);

                try {
                    // Configurazione del server SMTP
                    $mail->isSMTP();
                    $mail->Host = 'smtp.fdm91.net';  // Server SMTP
                    $mail->Port = 25;  // Porta SMTP
                    $mail->SMTPAuth = false;  // Nessuna autenticazione SMTP
                    $mail->SMTPSecure = false;  // Nessun SSL/TLS richiesto

                    // Mittente dell'email
                    $mail->setFrom('noreply@fdm91.net', 'Reset Password fdm91');

                    // Destinatario dell'email
                    $mail->addAddress($email);  // Email utente

                    // Contenuto dell'email
                    $mail->isHTML(true);  // Abilita il formato HTML
                    $mail->Subject = $subject;
                    $mail->Body = $message;

                    // Invio dell'email
                    $mail->send();
                    $success_message = "Le istruzioni per reimpostare la password sono state inviate alla tua email.";
                } catch (Exception $e) {
                    // Gestione degli errori
                    $error_message = "Errore nell'invio dell'email: {$mail->ErrorInfo}";
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
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="my.css" rel="stylesheet">
    <style>
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
        }
    </style>
</head>
<body>
    <div class="container login-container">
        <div class="card">
            <div class="card-header text-center">
                <h4>Reimposta la tua password</h4>
            </div>
            <div class="card-body">
                <?php
                if (!empty($error_message)) {
                    echo '<div class="alert alert-danger">' . htmlspecialchars($error_message) . '</div>';
                }
                if (!empty($success_message)) {
                    echo '<div class="alert alert-success">' . htmlspecialchars($success_message) . '</div>';
                }
                ?>
                <form action="passwordReset.php" method="post">
                    <div class="form-group">
                        <label for="email">Inserisci la tua email</label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="Indirizzo email" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">Invia il link di reimpostazione</button>
                    </div>
                    <p class="text-center"><a href="login.php">Torna al login</a></p>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS e dipendenze -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

