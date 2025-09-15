<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);
session_start();

require __DIR__ . '/vendor/autoload.php'; // PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'config.php';

if (isset($_SESSION['username'])) {
    header("location: index.php");
    exit;
}

/**
 * Invia email di notifica blocco account.
 */
function sendAccountLockedEmail(string $toEmail, string $username, array $config): bool {
    if (empty($toEmail)) return false;

    $resetUrl = rtrim($config['host_base'] ?? '', '/').'/passwordReset.php';

    $subject = 'Account bloccato';
    $body = "
        <p>Ciao <strong>".htmlspecialchars($username, ENT_QUOTES, 'UTF-8')."</strong>,</p>
        <p>Abbiamo rilevato troppi tentativi di accesso falliti al tuo account su <a href='https://my.fdm91.net'>MyFDM91</a>.</p>
        <p>Per sicurezza l’account è stato <strong>temporaneamente bloccato</strong>.</p>
        <p>Puoi sbloccarlo reimpostando la password da qui:<br>
        <a href=\"".htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8')."\">Password dimenticata</a></p>
        <p>Se non sei stato tu, contattaci all'indirizzo <a href='mailto:support@fdm91.net'>support@fdm91.net</a>.</p>
    ";

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $config['smtp']['host'];
        $mail->Port       = $config['smtp']['port'];
        $mail->SMTPAuth   = $config['smtp']['auth'];
        $mail->SMTPAutoTLS= $config['smtp']['autoTLS'];
        if (!empty($config['smtp']['options'])) {
            $mail->SMTPOptions = $config['smtp']['options'];
        }
        if (!empty($config['smtp']['username'])) $mail->Username = $config['smtp']['username'];
        if (!empty($config['smtp']['password'])) $mail->Password = $config['smtp']['password'];

        $mail->setFrom($config['email']['from_address'], $config['email']['from_name']);
        $mail->addAddress($toEmail);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        return $mail->send();
    } catch (Exception $e) {
        error_log('[login] sendAccountLockedEmail error: '.$e->getMessage());
        return false;
    }
}

/**
 * Incrementa badPasswordCount. Se arriva a 5: blocca, azzera contatore e invia email.
 */
function handleFailedLogin(mysqli $link, string $username, array $config): void {
    // Prendo id, email, contatore, enabled
    $sql = "SELECT id, email, badPasswordCount, enabled, username FROM users WHERE username = ?";
    if (!$stmt = $link->prepare($sql)) return;
    $stmt->bind_param("s", $username);
    if (!$stmt->execute()) { $stmt->close(); return; }
    $stmt->bind_result($id, $email, $bad, $enabled, $uname);
    if (!$stmt->fetch()) { $stmt->close(); return; }
    $stmt->close();

    if ((int)$enabled === 0) {
        // già bloccato: non fare nulla
        return;
    }

    $newBad = (int)$bad + 1;

    if ($newBad >= 5) {
        // blocca e azzera contatore
        $sql = "UPDATE users SET enabled = 0, badPasswordCount = 0 WHERE id = ?";
        if ($stmt = $link->prepare($sql)) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        }
        // invia email di blocco
        sendAccountLockedEmail($email ?? '', $uname ?? $username, $config);
    } else {
        // solo incrementa
        $sql = "UPDATE users SET badPasswordCount = ? WHERE id = ?";
        if ($stmt = $link->prepare($sql)) {
            $stmt->bind_param("ii", $newBad, $id);
            $stmt->execute();
            $stmt->close();
        }
    }
}

/**
 * Azzeramento contatore al login riuscito.
 */
function resetFailedCounter(mysqli $link, int $userId): void {
    $sql = "UPDATE users SET badPasswordCount = 0 WHERE id = ?";
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();
    }
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

	$sql = "SELECT id, username, name, email, password, badPasswordCount, enabled
			FROM users
			WHERE username = ? AND enabled = 1";

	if ($stmt = $link->prepare($sql)) {
		$stmt->bind_param("s", $username);

		if ($stmt->execute()) {
			$stmt->store_result();

			if ($stmt->num_rows == 1) {
				$stmt->bind_result($id, $dbUsername, $name, $email, $hashed_password, $badPasswordCount, $enabled);
				if ($stmt->fetch()) {
					if (password_verify($password, $hashed_password)) {
						// LOGIN OK
						// evita secondo session_start() -> è già stato chiamato
						session_regenerate_id(true);
						$_SESSION['id']       = $id;
						$_SESSION['username'] = $dbUsername;
						$_SESSION['name']     = $name;

						// reset contatore
						resetFailedCounter($link, $id);

						// remember me (invariato)...
						if ($remember_me) {
							$selector = bin2hex(random_bytes(8));
							$authenticator = random_bytes(33);

							setcookie(
								'rememberme',
								$selector . ':' . base64_encode($authenticator),
								time() + 2592000,
								'/',
								'fdm91.net', // TODO: metti il tuo dominio
								true, // secure
								true  // httponly
							);

							if ($stmt2 = $link->prepare("INSERT INTO auth_tokens (selector, token, user_id, username, expires) VALUES (?, ?, ?, ?, ?)")) {
								$stmt2->bind_param(
									'ssiss',
									$selector,
									hash('sha256', $authenticator),
									$id,
									$dbUsername,
									date('Y-m-d H:i:s', time() + 2592000)
								);
								$stmt2->execute();
								$stmt2->close();
							}
						}

						header("location: index.php");
						exit;
					} else {
						// PASSWORD ERRATA
						handleFailedLogin($link, $username, $config);
						$error_message = "E01 - Non è possibile eseguire il login con queste credenziali.";
					}
				}
			} else {
				// UTENTE NON TROVATO o DISABILITATO
				handleFailedLogin($link, $username, $config);
				$error_message = "E02 - Non è possibile eseguire il login con queste credenziali.";
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
