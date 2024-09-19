<?php
// Start a secure session with enhanced settings
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); // Ensure your site uses HTTPS
ini_set('session.use_strict_mode', 1);
session_start();

// Redirect already logged-in users to the dashboard
if (isset($_SESSION['username'])) {
    header("Location: dashboard.php");
    exit;
}

require_once 'config.php';

// Initialize messages
$error_message = '';
$success_message = '';

// Generate a simple CAPTCHA (optional, since we're adding reCAPTCHA)
$verification_num1 = rand(1, 10);
$verification_num2 = rand(1, 10);
$verification_answer = $verification_num1 + $verification_num2;

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error_message = "Richiesta non valida. Per favore riprova.";
    } else {
        // Validate reCAPTCHA
        if (!isset($_POST['g-recaptcha-response']) || empty($_POST['g-recaptcha-response'])) {
            $error_message = "Per favore, verifica di non essere un robot.";
        } else {
            // Your secret key
            $secret = '6LfGlkkqAAAAAD5Vxj0v69M1FRCv4sRAR-af3Qvd';

            // Verify the reCAPTCHA response
            $recaptcha_response = $_POST['g-recaptcha-response'];
            $remote_ip = $_SERVER['REMOTE_ADDR'];

            // Make and decode POST request:
            $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
            $recaptcha_data = [
                'secret' => $secret,
                'response' => $recaptcha_response,
                'remoteip' => $remote_ip
            ];

            $options = [
                'http' => [
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($recaptcha_data),
                ],
            ];
            $context  = stream_context_create($options);
            $verify = file_get_contents($recaptcha_url, false, $context);
            $captcha_success = json_decode($verify);

            if ($captcha_success->success != true) {
                $error_message = "Errore nella verifica reCAPTCHA. Per favore riprova.";
            } else {
                // Sanitize and validate input data
                $name = trim($_POST['name']);
                $surname = trim($_POST['surname']);
                $email = trim($_POST['email']);
                $username = trim($_POST['username']);
                $password = $_POST['password'];
                $confirm_password = $_POST['confirm_password'];
                $user_answer = trim($_POST['verification_answer']);

                // Basic validation
                if (empty($name) || empty($surname) || empty($email) || empty($username) || empty($password) || empty($confirm_password) || empty($user_answer)) {
                    $error_message = "Tutti i campi sono obbligatori.";
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error_message = "Indirizzo email non valido.";
                } elseif ($password !== $confirm_password) {
                    $error_message = "Le password non corrispondono.";
                } elseif ((int)$user_answer !== $verification_answer) {
                    $error_message = "Sei proprio sicuro di essere un umano?";
                } else {
                    // Check if username or email already exists
                    $sql = "SELECT id FROM users WHERE username = ? OR email = ?";
                    if ($stmt = $link->prepare($sql)) {
                        $stmt->bind_param("ss", $username, $email);
                        if ($stmt->execute()) {
                            $stmt->store_result();
                            if ($stmt->num_rows > 0) {
                                $error_message = "Il nome utente o l'indirizzo email risultano già registrati.";
                            } else {
                                // Insert new user into the database
                                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                                $sql_insert = "INSERT INTO users (name, surname, email, username, password) VALUES (?, ?, ?, ?, ?)";
                                if ($stmt_insert = $link->prepare($sql_insert)) {
                                    $stmt_insert->bind_param("sssss", $name, $surname, $email, $username, $hashed_password);
                                    if ($stmt_insert->execute()) {
                                        $success_message = "Registrazione completata con successo! Ora puoi accedere.";
                                        // Optionally, redirect to login page
                                        // header("Location: login.php");
                                        // exit;
                                    } else {
                                        $error_message = "Qualcosa è andato storto. Riprova più tardi.";
                                    }
                                    $stmt_insert->close();
                                } else {
                                    $error_message = "Qualcosa è andato storto. Riprova più tardi.";
                                }
                            }
                        } else {
                            $error_message = "Qualcosa è andato storto. Riprova più tardi.";
                        }
                        $stmt->close();
                    } else {
                        $error_message = "Qualcosa è andato storto. Riprova più tardi.";
                    }
                }
            }
        }
        // Regenerate CSRF token after form submission
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    $link->close();
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrazione</title>
	<?php include 'script.php' ?>
    <!-- reCAPTCHA API -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
    <!-- Register Form -->
    <div class="container register-form">
        <div class="card">
            <div class="card-header text-center bg-primary text-white">
                <h2>Registrati su MyFDM91</h2>
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
                <form action="register.php" method="post" novalidate>
                    <!-- CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Nome</label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="Nome" required>
                        <div class="invalid-feedback">
                            Inserisci il tuo nome.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="surname" class="form-label">Cognome</label>
                        <input type="text" name="surname" id="surname" class="form-control" placeholder="Cognome" required>
                        <div class="invalid-feedback">
                            Inserisci il tuo cognome.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="Email" required>
                        <div class="invalid-feedback">
                            Inserisci un indirizzo email valido.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" name="username" id="username" class="form-control" placeholder="Username" required>
                        <div class="invalid-feedback">
                            Scegli un nome utente.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
                        <div class="invalid-feedback">
                            Inserisci una password.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Conferma Password</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Conferma Password" required>
                        <div class="invalid-feedback">
                            Conferma la tua password.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="verification_question" class="form-label">
                            Verifica di essere un umano: quanto fa <?= $verification_num1 ?> + <?= $verification_num2 ?>?
                        </label>
                        <input type="number" name="verification_answer" id="verification_question" class="form-control" placeholder="La tua risposta" required>
                        <div class="invalid-feedback">
                            Rispondi alla domanda di verifica.
                        </div>
                    </div>

                    <!-- Google reCAPTCHA Widget -->
                    <div class="mb-3">
                        <div class="g-recaptcha" data-sitekey="6LfGlkkqAAAAAN0IBdrheLb7z0yyDuN4IHW2Tia_"></div>
                        <div class="invalid-feedback d-block" id="recaptcha-error" style="display: none;">
                            Per favore, verifica di non essere un robot.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary w-100">Registrati</button>
                    </div>
                    
                    <p class="text-center">Hai gi&agrave un account? <a href="login.php" class="text-decoration-none">Accedi qui</a>.</p>
                </form>
            </div>
        </div>
    </div>
    <script>
        (function () {
            'use strict'
            // Fetch all the forms we want to apply custom Bootstrap validation styles to
            var forms = document.querySelectorAll('form')

            // Loop over them and prevent submission
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        // Check if reCAPTCHA is checked
                        var recaptcha = grecaptcha.getResponse();
                        if (recaptcha.length === 0) {
                            // Not verified
                            event.preventDefault()
                            event.stopPropagation()
                            document.getElementById('recaptcha-error').style.display = 'block';
                        } else {
                            document.getElementById('recaptcha-error').style.display = 'none';
                        }

                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }

                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
</body>
</html>
