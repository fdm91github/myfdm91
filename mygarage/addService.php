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
    $attachment_path = null;

    // Check if a file was uploaded
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        // Define the target directory for uploads
        $targetDir = 'uploads/';
        // Generate a unique name for the uploaded file
        $fileName = basename($_FILES['attachment']['name']);
        $targetFilePath = $targetDir . uniqid() . '_' . $fileName;

        // Move the uploaded file to the target directory
        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $targetFilePath)) {
            // File upload succeeded
            $attachment_path = $targetFilePath;
        } else {
            $response["message"] = "Errore durante il caricamento del file.";
            echo json_encode($response);
            exit;
        }
    }

    // Validate the data
    if (empty($vehicle_id) || empty($description) || empty($amount) || empty($buying_date) || empty($registered_kilometers)) {
        $response["message"] = "Inserisci tutti i campi obbligatori.";
    } else {
        $sql = "INSERT INTO vehicle_services (user_id, vehicle_id, description, amount, buying_date, registered_kilometers, attachment_path) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        if ($stmt = $link->prepare($sql)) {
            $stmt->bind_param("iisssis", $user_id, $vehicle_id, $description, $amount, $buying_date, $registered_kilometers, $attachment_path);
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
