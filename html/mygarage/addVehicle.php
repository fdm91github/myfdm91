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
    $buying_date = trim($_POST["buying_date"]);
    $registration_date = trim($_POST["registration_date"]);
    $plate_number = trim($_POST["plate"]);
    $chassis_number = trim($_POST["chassis_number"]);
    $tax_month = trim($_POST["tax_expiry_month"]);

    // Convalido i dati
    if (empty($description) || empty($buying_date) || empty($registration_date) || empty($plate_number) || empty($chassis_number) || empty($tax_month)) {
        $response["message"] = "Inserisci tutti i campi obbligatori.";
    } else {
        // Verifico la presenza di duplicati
        $check_sql = "SELECT id FROM vehicles WHERE user_id = ? AND plate_number = ? AND chassis_number = ?";
        if ($check_stmt = $link->prepare($check_sql)) {
            $check_stmt->bind_param("iss", $user_id, $plate_number, $chassis_number);
            $check_stmt->execute();
            $check_stmt->store_result();
            
            if ($check_stmt->num_rows > 0) {
                $response["message"] = "Il veicolo che stai provando ad aggiungere esiste già.";
            } else {
                $sql = "INSERT INTO vehicles (user_id, description, buying_date, registration_date, plate_number, chassis_number, tax_month) VALUES (?, ?, ?, ?, ?, ?, ?)";
                if ($stmt = $link->prepare($sql)) {
                    $stmt->bind_param("issssss", $user_id, $description, $buying_date, $registration_date, $plate_number, $chassis_number, $tax_month);
                    if ($stmt->execute()) {
                        $response["status"] = "success";
                        $response["message"] = "Veicolo aggiunto con successo!";
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
