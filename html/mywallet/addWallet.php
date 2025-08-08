<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("location: ../login.php");
    exit;
}

require_once '../config.php';

$response = ["status" => "error", "message" => ""];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['id'];
    $description = trim($_POST["description"]);
    $icon = isset($_POST["icon"]) ? trim($_POST["icon"]) : "";
    $show_in_dashboard = isset($_POST["show_in_dashboard"]) ? 1 : 0;

    // Controlla che tutti i campi obbligatori siano stati inseriti
    if (empty($description) || empty($icon)) {
        $response["message"] = "Inserisci tutti i campi obbligatori.";
    } else {
        // Verifica la presenza di duplicati per lo stesso utente e descrizione
        $check_sql = "SELECT id FROM wallets WHERE user_id = ? AND description = ?";
        if ($check_stmt = $link->prepare($check_sql)) {
            $check_stmt->bind_param("is", $user_id, $description);
            $check_stmt->execute();
            $check_stmt->store_result();
            
            if ($check_stmt->num_rows > 0) {
                $response["message"] = "Il portafoglio che stai provando ad aggiungere esiste già.";
            } else {
                $sql = "INSERT INTO wallets (user_id, description, icon, show_in_dashboard) VALUES (?, ?, ?, ?)";
                if ($stmt = $link->prepare($sql)) {
                    $stmt->bind_param("issi", $user_id, $description, $icon, $show_in_dashboard);
                    if ($stmt->execute()) {
                        $response["status"] = "success";
                        $response["message"] = "Portafoglio aggiunto con successo!";
                    } else {
                        $response["message"] = "Qualcosa è andato storto. Riprova più tardi.";
                    }
                    $stmt->close();
                } else {
                    $response["message"] = "Qualcosa è andato storto. Riprova più tardi.";
                }
            }
            $check_stmt->close();
        }
    }
    $link->close();
}

header('Content-Type: application/json');
echo json_encode($response);
?>
