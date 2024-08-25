<?php
session_start();

$response = ["status" => "error", "message" => "", "service_id" => null];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once '../config.php';

    $user_id = $_SESSION['id'];
    $vehicle_id = trim($_POST['vehicle_id']);
    $description = trim($_POST["description"]);
    $amount = trim($_POST["amount"]);
    $buying_date = trim($_POST["buying_date"]);
    $registered_kilometers = trim($_POST["registered_kilometers"]);

    // Convalido i dati
    if (empty($vehicle_id) || empty($description) || empty($amount) || empty($buying_date) || empty($registered_kilometers)) {
        $response["message"] = "Inserisci tutti i campi obbligatori.";
    } else {
        $sql = "INSERT INTO vehicle_services (user_id, vehicle_id, description, amount, buying_date, registered_kilometers) VALUES (?, ?, ?, ?, ?, ?)";
        if ($stmt = $link->prepare($sql)) {
            $stmt->bind_param("iisssi", $user_id, $vehicle_id, $description, $amount, $buying_date, $registered_kilometers);
            if ($stmt->execute()) {
                $response["status"] = "success";
                $response["message"] = "Manutenzione aggiunta con successo!";
                $response["service_id"] = $link->insert_id;  // Get the last inserted service ID
            } else {
                $response["message"] = "Qualcosa è andato storto. Riprova più tardi.";
            }
            $stmt->close();
        } else {
            $response["message"] = "Qualcosa è andato storto. Riprova più tardi.";
        }
    }
    $link->close();
}

header('Content-Type: application/json');
echo json_encode($response);
?>
