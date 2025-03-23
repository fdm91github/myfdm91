<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("location: ../login.php");
    exit;
}

require_once '../config.php';

$user_id = $_SESSION['id'];
$wallet_id = $_GET['id'] ?? null;

$response = ["status" => "error", "message" => ""];

if (!$wallet_id) {
    $response["message"] = "Richiesta non valida a causa di wallet_id non fornito.";
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $description = trim($_POST['description']);
    $icon = isset($_POST['icon']) ? trim($_POST['icon']) : "";
    $show_in_dashboard = isset($_POST['show_in_dashboard']) ? 1 : 0;

    // Validazione dei campi obbligatori
    if (empty($description) || empty($icon)) {
        $response["message"] = "Inserisci tutti i campi obbligatori.";
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    $sql = "UPDATE wallets SET description = ?, icon = ?, show_in_dashboard = ? WHERE id = ? AND user_id = ?";
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("ssiii", $description, $icon, $show_in_dashboard, $wallet_id, $user_id);
        if ($stmt->execute()) {
            $response["status"] = "success";
            $response["message"] = "Portafoglio aggiornato con successo!";
        } else {
            $response["message"] = "Qualcosa è andato storto. Riprova più tardi.";
        }
        $stmt->close();
    } else {
        $response["message"] = "Qualcosa è andato storto. Riprova più tardi.";
    }

    $link->close();
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>
