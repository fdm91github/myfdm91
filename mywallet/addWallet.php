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
        // Validazione dell'icona
        $allowed_icons = ["cart4", "bag-check-fill", "bag-heart-fill", "bag-plus-fill", "bag-dash-fill", "bag-fill", "bag-x-fill", "suitcase-lg-fill", "suitcase2-fill", "bandaid-fill", "bar-chart-fill", "graph-up", "basket3-fill", "safe-fill", "bank2", "cash-coin", "piggy-bank-fill", "amazon", "google", "paypal", "microsoft", "airplane-fill", "bus-front-fill", "car-front-fill", "backpack2-fill", "cake2-fill", "gift-fill", "calculator-fill", "calendar-week", "cloud-fill", "controller", "credit-card-fill", "cup-hot-fill", "currency-dollar", "currency-euro", "currency-pound", "device-hdd-fill", "envelope-at-fill", "ev-station-fill", "hammer", "house-fill", "mailbox2-flag", "music-note-beamed", "p-circle-fill", "peson-fill", "pie-chhart-fill", "sim-fill", "ticket-perforated-fill"];
        if (!in_array($icon, $allowed_icons)) {
            $response["message"] = "Icona non valida.";
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
    }
    $link->close();
}

header('Content-Type: application/json');
echo json_encode($response);
?>
