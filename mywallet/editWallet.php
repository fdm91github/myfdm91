<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("location: ../login.php");
    exit;
}

require_once '../config.php';

$user_id = $_SESSION['id'];
$wallet_id = $_GET['id'] ?? null;

$error_message = '';
$success_message = '';

if ($wallet_id) {
    $sql = "SELECT description FROM wallets WHERE id = ? AND user_id = ?";
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("ii", $wallet_id, $user_id);
        $stmt->execute();
        $stmt->bind_result($description);
        $stmt->fetch();
        $stmt->close();
    } else {
        $error_message = "Qualcosa è andato storto. Riprova più tardi.";
        exit;
    }
} else {
    $error_message = "Richiesta non valida a causa di wallet_id non fornito.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $description = trim($_POST['description']);

    // Validate inputs
    if (empty($description)) {
        $error_message = "Inserisci tutti i campi obbligatori.";
    } else {
        $sql = "UPDATE wallets SET description = ? WHERE id = ? AND user_id = ?";
        if ($stmt = $link->prepare($sql)) {
            $stmt->bind_param("sii", $description, $wallet_id, $user_id);
            if ($stmt->execute()) {
                $success_message = "Portafogli modificato con successo. Reindirizzamento alla dashboard...";
                header("refresh:3;url=dashboard.php");
            } else {
                $error_message = "Qualcosa è andato storto. Riprova più tardi.";
            }
            $stmt->close();
        } else {
            $error_message = "Qualcosa è andato storto. Riprova più tardi.";
        }
    }
    $link->close();
}
?>
