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
                        $verification_link = "https://my.fdm91.net/mysettings/verify_email.php?token=$token";

                        // Email content
                        $subject = "Verifica la tua email";
                        $message = "Clicca su questo link per verificare il tuo indirizzo email: 
                                    <a href='" . htmlspecialchars($verification_link) . "'>Verifica Email</a>";

                        // Use PHPMailer to send the email
                        $mail = new PHPMailer(true);

                        try {
                            // SMTP server configuration
                            $mail->isSMTP();
                            $mail->Host = 'smtp.fdm91.net';  // SMTP server
                            $mail->Port = 25;  // SMTP port
                            $mail->SMTPAuth = false;  // No SMTP authentication
                            $mail->SMTPSecure = false;  // No SSL/TLS

                            // Sender info
                            $mail->setFrom('no-reply@fdm91.net', 'MyFDM91');

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

