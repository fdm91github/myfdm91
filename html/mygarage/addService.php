<?php
session_start();

$response = ["status" => "error", "message" => "", "service_id" => null];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once '../config.php';

    $response = ["status" => "error", "message" => "", "service_id" => null];
    $user_id = $_SESSION['id'];
    $vehicle_id = trim($_POST['vehicle_id']);
    $description = trim($_POST["description"]);
    $amount = trim($_POST["amount"]);
    $buying_date = trim($_POST["buying_date"]);
    $registered_kilometers = trim($_POST["registered_kilometers"]);

    // Parts data
    $part_names = $_POST['part_name'] ?? [];
    $part_numbers = $_POST['part_number'] ?? [];

    // Handle file upload
    $attachment_path = null;
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['attachment']['tmp_name'];
        $fileName = $_FILES['attachment']['name'];
        $fileSize = $_FILES['attachment']['size'];
        $fileType = $_FILES['attachment']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Define allowed file extensions
        $allowedfileExtensions = array('jpg', 'jpeg', 'png', 'pdf');

        if (in_array($fileExtension, $allowedfileExtensions)) {
            // Create unique file name to prevent file name clashes
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = './uploads/';
            $dest_path = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $attachment_path = $dest_path;  // Save the file path for database insertion
            } else {
                $response["message"] = 'Errore durante il caricamento del file:' . $_FILES['attachment']['error'];
                echo json_encode($response);
                exit;
            }
        } else {
            $response["message"] = 'Tipo di file non supportato. Solo .jpg, .jpeg, .png, .pdf sono permessi.';
            echo json_encode($response);
            exit;
        }
    }

    // Validate inputs
    if (empty($vehicle_id) || empty($description) || empty($buying_date) || empty($registered_kilometers)) {
        $response["message"] = "Inserisci tutti i campi obbligatori.";
    } else {
        // Begin transaction
        $link->begin_transaction();
        try {
            // Insert the main service record
            $sql = "INSERT INTO vehicle_services (user_id, vehicle_id, description, amount, buying_date, registered_kilometers, attachment_path) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            if ($stmt = $link->prepare($sql)) {
                $stmt->bind_param("iisssis", $user_id, $vehicle_id, $description, $amount, $buying_date, $registered_kilometers, $attachment_path);
                if ($stmt->execute()) {
                    $response["status"] = "success";
                    $response["message"] = "Manutenzione aggiunta con successo!";
                    $response["service_id"] = $link->insert_id;  // Get the last inserted service ID
                } else {
                    throw new Exception("Errore nell'inserimento della manutenzione: " . $stmt->error);
                }
                $stmt->close();
            } else {
                throw new Exception("Errore nella preparazione della query: " . $link->error);
            }

            // Insert parts if provided
            foreach ($part_names as $index => $part_name) {
                $part_number = $part_numbers[$index] ?? '';

                $sql = "INSERT INTO vehicle_service_parts (user_id, service_id, part_name, part_number) 
                        VALUES (?, ?, ?, ?)";
                if ($stmt = $link->prepare($sql)) {
                    $stmt->bind_param("iiss", $user_id, $response["service_id"], $part_name, $part_number);
                    if (!$stmt->execute()) {
                        throw new Exception("Errore nell'inserimento delle parti: " . $stmt->error);
                    }
                    $stmt->close();
                } else {
                    throw new Exception("Errore nella preparazione della query per le parti: " . $link->error);
                }
            }

            // Commit the transaction
            $link->commit();

        } catch (Exception $e) {
            // Rollback transaction on error
            $link->rollback();
            $response["status"] = "error";
            $response["message"] = $e->getMessage();
        }
    }

    $link->close();
}

header('Content-Type: application/json');
echo json_encode($response);
?>
