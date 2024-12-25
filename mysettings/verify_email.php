<?php
require_once '../config.php';

$token = $_GET['token'] ?? '';

if (!$token) {
    die("Token non valido.");
}

$sql = "SELECT user_id, email FROM email_verifications WHERE token = ? AND expires >= ?";
if ($stmt = $link->prepare($sql)) {
    $current_time = time();
    $stmt->bind_param("si", $token, $current_time);
    if ($stmt->execute()) {
        $stmt->store_result();
        if ($stmt->num_rows === 1) {
            $stmt->bind_result($user_id, $email);
            $stmt->fetch();

            $sql = "UPDATE users SET verified = 1 WHERE id = ?";
            if ($stmt = $link->prepare($sql)) {
                $stmt->bind_param("i", $user_id);
                if ($stmt->execute()) {
                    // Delete the token after successful verification
                    $sql = "DELETE FROM email_verifications WHERE token = ?";
                    if ($stmt = $link->prepare($sql)) {
                        $stmt->bind_param("s", $token);
                        $stmt->execute();
                    }
		    echo json_encode(['success' => true, 'message' => 'Email verificata con successo.']);
		    header("Location: https://my.fdm91.net/mysettings?email_verified=1");
		    exit;
                }
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Token non valido o scaduto.']);
        }
    }
}
$link->close();

echo json_encode(['success' => false, 'message' => 'Errore durante la verifica.']);

