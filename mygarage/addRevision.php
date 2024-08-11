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
    $buying_date = trim($_POST["buying_date"]);

    // Validate inputs
    if (empty($vehicle_id) || empty($amount) || empty($buying_date)) {
        $response["message"] = "Inserisci tutti i campi obbligatori.";
    } else {
        // Check for duplicate entry
        $check_sql = "SELECT id FROM vehicle_revisions WHERE user_id = ? AND vehicle_id = ? AND buying_date = ?";
        if ($check_stmt = $link->prepare($check_sql)) {
            $check_stmt->bind_param("iis", $user_id, $vehicle_id, $buying_date);
            $check_stmt->execute();
            $check_stmt->store_result();
            
            if ($check_stmt->num_rows > 0) {
                $response["message"] = "Il veicolo ha già una revisione per la stessa data.";
            } else {
                // Insert new entry if no duplicate found
                $sql = "INSERT INTO vehicle_revisions (user_id, vehicle_id, amount, buying_date) VALUES (?, ?, ?, ?)";
                if ($stmt = $link->prepare($sql)) {
                    // Note that we should pass only four parameters here, matching the four columns in the INSERT statement.
                    $stmt->bind_param("iiss", $user_id, $vehicle_id, $amount, $buying_date);
                    if ($stmt->execute()) {
                        $response["status"] = "success";
                        $response["message"] = "Revisione aggiunta con successo!";
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
