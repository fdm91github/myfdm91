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
    $vehicle_id = trim($_POST['vehicle_id']); // Get the vehicle_id from the POST data
    $amount = trim($_POST["amount"]);
    $date = trim($_POST["date"]);

    // Convalido i dati
    if (empty($vehicle_id) || empty($amount) || empty($date)) {
        $response["message"] = "Inserisci tutti i campi obbligatori.";
    } else {
        // Verifico la presenza di duplicati
        $check_sql = "SELECT id FROM vehicle_km_registered WHERE user_id = ? AND vehicle_id = ? AND date = ?";
        if ($check_stmt = $link->prepare($check_sql)) {
            $check_stmt->bind_param("iis", $user_id, $vehicle_id, $date);
            $check_stmt->execute();
            $check_stmt->store_result();
            
            if ($check_stmt->num_rows > 0) {
                $response["message"] = "Il veicolo ha già una percorrenza registrata per la stessa data.";
            } else {
                $sql = "INSERT INTO vehicle_km_registered (user_id, vehicle_id, kilometers, date) VALUES (?, ?, ?, ?)";
                if ($stmt = $link->prepare($sql)) {
                    $stmt->bind_param("iiss", $user_id, $vehicle_id, $amount, $date);
                    if ($stmt->execute()) {
                        $response["status"] = "success";
                        $response["message"] = "Percorrenza aggiunta con successo!";
                    } else {
                        $response["message"] = "Qualcosa è andato storto. Riprova più tardi.";
                    }
                    $stmt->close();
                } else {
                    $response["message"] = "Qualcosa è andato storto. Riprova più tardi.";
                }
            }
            $check_stmt->close();
        } else {
            $response["message"] = "Qualcosa è andato storto. Riprova più tardi.";
        }
    }
    $link->close();
}

header('Content-Type: application/json');
echo json_encode($response);
?>
