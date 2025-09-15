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

function sendVerificationEmailJson(string $email): array {
    // Costruisco l'URL assoluto allo stesso modo del browser
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $endpoint = $scheme . '://' . $host . '/mysettings/send_verification_email.php';

    $ch = curl_init($endpoint);
    $payload = json_encode(['email' => $email], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
    ]);

    $res  = curl_exec($ch);
    $cerr = curl_error($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($res === false) {
        error_log("[register] sendVerificationEmailJson cURL error: $cerr");
        return [false, "Errore di rete nell'invio dell'email di verifica."];
    }

    $data = json_decode($res, true);
    if ($code === 200 && is_array($data) && !empty($data['success'])) {
        return [true, $data['message'] ?? 'Email di verifica inviata.'];
    }

    $msg = is_array($data) ? ($data['message'] ?? 'Errore sconosciuto') : "HTTP $code";
    error_log("[register] sendVerificationEmailJson response error: $msg");
    return [false, "Non è stato possibile inviare l'email di verifica."];
}

// Initialize messages
$error_message = '';
$success_message = '';

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
        if (!isset($_POST['recaptcha_token']) || empty($_POST['recaptcha_token'])) {
            $error_message = "Errore nella verifica reCAPTCHA. Per favore riprova.";
        } else {
            // Your secret key
            $secret = '6LfGlkkqAAAAAD5Vxj0v69M1FRCv4sRAR-af3Qvd'; // Replace with your Secret Key

            // Verify the reCAPTCHA response
            $recaptcha_token = $_POST['recaptcha_token'];
            $remote_ip = $_SERVER['REMOTE_ADDR'];

            // Prepare the POST request to Google's API using cURL
            $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
            $recaptcha_data = [
                'secret' => $secret,
                'response' => $recaptcha_token,
                'remoteip' => $remote_ip
            ];

            $ch = curl_init($recaptcha_url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($recaptcha_data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $recaptcha_response = curl_exec($ch);
            curl_close($ch);

            $captcha_success = json_decode($recaptcha_response);

            if ($captcha_success->success != true || $captcha_success->score < 0.5) { // Adjust score threshold as needed
                $error_message = "La verifica reCAPTCHA non è riuscita. Per favore riprova.";
            } else {
                // Sanitize and validate input data
                $name = trim($_POST['name']);
                $surname = trim($_POST['surname']);
                $email = trim($_POST['email']);
                $username = trim($_POST['username']);
                $password = $_POST['password'];
                $confirm_password = $_POST['confirm_password'];

                // Basic validation
                if (empty($name) || empty($surname) || empty($email) || empty($username) || empty($password) || empty($confirm_password)) {
                    $error_message = "Tutti i campi sono obbligatori.";
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error_message = "Indirizzo email non valido.";
                } elseif ($password !== $confirm_password) {
                    $error_message = "Le password non corrispondono.";
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
										// Invia la mail di verifica con lo stesso POST JSON del fetch()
										[$okMail, $mailMsg] = sendVerificationEmailJson($email);

										// Messaggio non-bloccante (non impediamo la registrazione se l'email fallisce)
										if ($okMail) {
											$success_message = "Registrazione completata! Ti abbiamo inviato un’email per verificare l’indirizzo.";
										} else {
											$success_message = "Registrazione completata! Tuttavia non siamo riusciti a inviare l’email di verifica. Puoi riprovare da Impostazioni > Verifica email.";
										}

										// Redirect alla login con flag per mostrare un banner
										header("Location: login.php?registered=1");
										exit;
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
    <!-- reCAPTCHA v3 API -->
    <script src="https://www.google.com/recaptcha/api.js?render=6LfGlkkqAAAAAN0IBdrheLb7z0yyDuN4IHW2Tia_"></script>
</head>
<body>
    <!-- Register Form -->
    <div class="container register-form">
        <div class="card register-card">
            <div class="card-header text-center">
                <h4>Registrati su MyFDM91</h4>
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

                    <!-- Google reCAPTCHA v3 Token -->
                    <input type="hidden" name="recaptcha_token" id="recaptcha_token">

                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary w-100">Registrati</button>
                    </div>
                    
                    <p class="text-center">Hai gi&agrave un account? <a href="login.php" class="text-decoration-none">Accedi qui</a>.</p>
                </form>
            </div>
        </div>
    </div>

    <!-- Google reCAPTCHA v3 Integration -->
    <script>
        grecaptcha.ready(function() {
            grecaptcha.execute('6LfGlkkqAAAAAN0IBdrheLb7z0yyDuN4IHW2Tia_', {action: 'register'}).then(function(token) {
                // Add your logic to submit to your backend server here.
                var recaptchaToken = document.getElementById('recaptcha_token');
                recaptchaToken.value = token;
            });
        });

        // Bootstrap form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('form')

            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
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
