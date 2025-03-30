<?php
require_once '../config.php';
require '../vendor/autoload.php'; // Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);
    $email = trim($data['email']);

    // Check if the email exists
    $sql = "SELECT id FROM users WHERE email = ?";
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            $stmt->store_result();
            if ($stmt->num_rows == 1) {
                // Generate a unique verification token
                $token = bin2hex(random_bytes(50));
                $stmt->bind_result($user_id);
                $stmt->fetch();

                // Set expiration time (1 hour from now)
                $expires = date("U") + 3600;

                // Insert or update the token in the database
                $sql = "INSERT INTO email_verifications (user_id, email, token, expires) VALUES (?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE token = VALUES(token), expires = VALUES(expires)";
                if ($stmt = $link->prepare($sql)) {
                    $stmt->bind_param("issi", $user_id, $email, $token, $expires);
                    if ($stmt->execute()) {
                        // Create the verification link
                        $verification_link = $config['host_base'] . "/mysettings/verify_email.php?token=$token";

                        // Email content
                        $subject = "Verifica la tua email";
                        $message = "<p>Clicca su questo link per verificare il tuo indirizzo email:</p>
                                    <a href='" . htmlspecialchars($verification_link) . "'>Verifica Email</a>";

                        // Use PHPMailer to send the email
                        $mail = new PHPMailer(true);

                        try {
                            // SMTP server configuration
							$mail->isSMTP();
							$mail->Host = $config['smtp']['host'];
							$mail->Port = $config['smtp']['port'];
							$mail->SMTPAuth = $config['smtp']['auth'];
							$mail->SMTPAutoTLS = $config['smtp']['autoTLS'];
							$mail->SMTPOptions = $config['smtp']['options'];

                            // Sender info
                            $mail->setFrom($config['email']['from_address'], $config['email']['from_name']);

                            // Recipient
                            $mail->addAddress($email);

                            // Email content
                            $mail->isHTML(true);  // Enable HTML format
                            $mail->Subject = $subject;
                            $mail->Body = $message;

                            // Send the email
                            $mail->send();
                            echo json_encode(['success' => true, 'message' => 'Email di verifica inviata con successo!']);
                        } catch (Exception $e) {
                            // Handle errors
                            echo json_encode(['success' => false, 'message' => 'Errore nell\'invio dell\'email: ' . htmlspecialchars($mail->ErrorInfo)]);
                        }
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Errore durante il salvataggio del token.']);
                    }
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Email non trovata.']);
            }
        }
        $stmt->close();
    }
    $link->close();
}
?>

