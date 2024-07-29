<?php
session_start();
require_once 'config.php';

// Check if the user is already logged in, if yes then redirect to the selection page
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

    $sql = "SELECT id, username, name, password FROM users WHERE username = ?";

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
                                'website.com', // Change this to your domain
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
                        $error_message = "Incorrect password.";
                    }
                }
            } else {
                $error_message = "No user found with username $username.";
            }
        } else {
            $error_message = "Something went wrong. Please try again later.";
        }
        $stmt->close();
    }
    $link->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
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
                <h4>Accedi per continuare</h4>
            </div>
            <div class="card-body">
                <form action="login.php" method="post">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" class="form-control" placeholder="Inserisci qui il tuo nome utente :)" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Ora inserisci la tua password!" required>
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" name="remember_me" id="remember_me" class="form-check-input">
                        <label for="remember_me" class="form-check-label">Ricordami per 30 giorni</label>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">Accedi</button>
                    </div>
                    <p class="text-center">Non hai ancora un account? Cosa aspetti, <a href="register.php">registrati</a>!</p>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="errorModalLabel">Error</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p id="errorMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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

