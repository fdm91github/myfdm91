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
    $company = trim($_POST["company"]);
    $amount = trim($_POST["amount"]);
    $buying_date = trim($_POST["buying_date"]);

    // Convalido i dati
    if (empty($vehicle_id) || empty($company) || empty($amount) || empty($buying_date)) {
        $response["message"] = "Inserisci tutti i campi obbligatori.";
    } else {
        // Verifico la presenza di duplicati
        $check_sql = "SELECT id FROM vehicle_insurances WHERE user_id = ? AND vehicle_id = ? AND buying_date = ?";
        if ($check_stmt = $link->prepare($check_sql)) {
            $check_stmt->bind_param("iis", $user_id, $vehicle_id, $buying_date);
            $check_stmt->execute();
            $check_stmt->store_result();
            
            if ($check_stmt->num_rows > 0) {
                $response["message"] = "Il veicolo ha già un'assicurazione per la stessa data.";
            } else {
                $sql = "INSERT INTO vehicle_insurances (user_id, vehicle_id, company, amount, buying_date) VALUES (?, ?, ?, ?, ?)";
                if ($stmt = $link->prepare($sql)) {
                    $stmt->bind_param("iisss", $user_id, $vehicle_id, $company, $amount, $buying_date);
                    if ($stmt->execute()) {
                        $response["status"] = "success";
                        $response["message"] = "Assicurazione aggiunta con successo!";
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
