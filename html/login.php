<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);
session_start();

require_once 'config.php';

if (isset($_SESSION['username'])) {
    header("location: index.php");
    exit;
}

// Check if the remember me cookie exists
if (isset($_COOKIE['rememberme'])) {
    list($selector, $authenticator) = explode(':', $_COOKIE['rememberme']);

    $stmt = $link->prepare("SELECT * FROM auth_tokens WHERE selector = ?");
    $stmt->bind_param('s', $selector);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (hash_equals($row['token'], hash('sha256', base64_decode($authenticator)))) {
            session_regenerate_id();
            $_SESSION['id'] = $row['user_id'];
            $_SESSION['username'] = $row['username'];
            header("location: index.php");
            exit;
        }
    }
}

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $remember_me = isset($_POST['remember_me']);

    $sql = "SELECT id, username, name, password FROM users WHERE username = ? AND enabled = 1";

    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("s", $username);

        if ($stmt->execute()) {
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                $stmt->bind_result($id, $username, $name, $hashed_password);
                if ($stmt->fetch()) {
                    if (password_verify($password, $hashed_password)) {
                        session_start();
                        $_SESSION['id'] = $id;
                        $_SESSION['username'] = $username;
                        $_SESSION['name'] = $name;
                        
                        if ($remember_me) {
                            $selector = bin2hex(random_bytes(8));
                            $authenticator = random_bytes(33);

                            setcookie(
                                'rememberme',
                                $selector . ':' . base64_encode($authenticator),
                                time() + 2592000,
                                '/',
                                'fdm91.net', // Change this to your domain
                                true, // Secure, set to true if using HTTPS
                                true  // HttpOnly
                            );

                            $stmt = $link->prepare("INSERT INTO auth_tokens (selector, token, user_id, username, expires) VALUES (?, ?, ?, ?, ?)");
                            $stmt->bind_param(
                                'ssiss',
                                $selector,
                                hash('sha256', $authenticator),
                                $id,
                                $username,
                                date('Y-m-d H:i:s', time() + 2592000)
                            );
                            $stmt->execute();
                        }

                        header("location: index.php");
                        exit;
                    } else {
                        $error_message = "E01 - Non è possibile eseguire il login con queste credenziali."; // password errata
                    }
                }
            } else {
                $error_message = "E02 - Non è possibile eseguire il login con queste credenziali."; // nessun utente trovato
            }
        } else {
            $error_message = "Qualcosa è andato storto. Riprova più tardi.";
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
    <title>Login</title>
    <?php include 'script.php' ?>
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
    <!-- Login Form -->
    <div class="container login-container">
        <div class="card">
            <div class="card-header text-center">
                <h4>Accedi per continuare</h4>
            </div>
            <div class="card-body">
                <form action="login.php" method="post">
                    <div class="mb-3">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" class="form-control" placeholder="Inserisci qui il tuo nome utente :)" required>
                    </div>
                    <div class="mb-3">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Ora inserisci la tua password!" required>
                    </div>
		    <div class="mb-3 form-check">
			<input type="checkbox" name="remember_me" id="remember_me" class="form-check-input">
                        <label for="remember_me" class="form-check-label">Ricordami per 30 giorni</label>
                    </div>
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary w-100">Accedi</button>
                    </div>
                    <p class="text-center">Non hai ancora un account? Cosa aspetti, <a style="color:white;" href="register.php">registrati</a>!</p>
                    <p class="text-center"><a style="color:white;" href="passwordReset.php">Ho dimenticato la password</a></p>
                    <p class="text-center"><a style="color:white;" href="mailto:support@fdm91.net">Contatti</a></p>
                </form>
            </div>
        </div>
    </div>

    <!-- Error Modal -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="errorModalLabel">Errore</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"></span>
                    </button>
                </div>
                <div class="modal-body">
                    <p id="errorMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            <?php if (!empty($error_message)): ?>
                $('#errorMessage').text(<?php echo json_encode($error_message); ?>);
                $('#errorModal').modal('show');
            <?php endif; ?>
        });
    </script>
</body>
</html>
