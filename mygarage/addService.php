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
    
    // Handle file upload
    $attachment_path = null;
	if (isset($_FILES['attachment'])) {
		error_log(print_r($_FILES, true));  // This will log the file details into your PHP error log
	} else {
		error_log('No file uploaded.');
	}
	
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

            if(move_uploaded_file($fileTmpPath, $dest_path)) {
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

    // Convalido i dati
    if (empty($vehicle_id) || empty($description) || empty($amount) || empty($buying_date) || empty($registered_kilometers)) {
        $response["message"] = "Inserisci tutti i campi obbligatori.";
    } else {
        $sql = "INSERT INTO vehicle_services (user_id, vehicle_id, description, amount, buying_date, registered_kilometers, attachment_path) VALUES (?, ?, ?, ?, ?, ?, ?)";
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
