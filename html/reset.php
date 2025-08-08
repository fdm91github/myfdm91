<?php
// Impostazioni di sicurezza per la sessione
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);

require_once 'config.php';
$error_message = '';
$success_message = '';

if (isset($_GET['token']) && isset($_GET['email'])) {
    $token = $_GET['token'];
    $email = $_GET['email'];

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $new_password = trim($_POST['new_password']);
        $confirm_password = trim($_POST['confirm_password']);

        // Verifico che i campi siano completi e validi
        if (empty($new_password) || empty($confirm_password)) {
            $error_message = "Per favore, compila tutti i campi.";
        } elseif ($new_password != $confirm_password) {
            $error_message = "Le password non corrispondono.";
        } elseif (strlen($new_password) < 8) {
            $error_message = "La password deve essere di almeno 8 caratteri.";
        } else {
            // Verifico il token e la sua validità
            $sql = "SELECT user_id FROM password_resets WHERE email = ? AND token = ? AND expires >= ?";
            if ($stmt = $link->prepare($sql)) {
                $current_time = date("U");
                $stmt->bind_param("ssi", $email, $token, $current_time);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($user_id);
                    $stmt->fetch();

                    // Aggiorno la password dell'utente
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $sql = "UPDATE users SET password = ? WHERE id = ?";
                    if ($stmt = $link->prepare($sql)) {
                        $stmt->bind_param("si", $hashed_password, $user_id);
                        $stmt->execute();

                        $success_message = "La tua password è stata reimpostata con successo.";

                        // Elimino il token di reset
                        $sql = "DELETE FROM password_resets WHERE email = ?";
                        $stmt = $link->prepare($sql);
                        $stmt->bind_param("s", $email);
                        $stmt->execute();
                    }
                } else {
                    $error_message = "Token non valido o scaduto.";
                }
            }
            $stmt->close();
        }
    }
} else {
    // Se non ci sono parametri, reindirizzo al login
    header("location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reimposta la password</title>
	<?php include "script.php" ?>
</head>
<body>
    <div class="container reset-container">
        <div class="card reset-card">
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
                <form action="" method="post">
                    <div class="mb-3">
                        <label for="new_password">Nuova Password</label>
                        <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Inserisci la nuova password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password">Conferma Password</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Conferma la nuova password" required>
                    </div>
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary btn-block">Reimposta la password</button>
                    </div>
                    <p class="text-center"><a href="login.php">Torna al login</a></p>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

