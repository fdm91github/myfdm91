<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("location: ../login.php");
    exit;
}

require_once '../config.php';

$user_id = $_SESSION['id'];
$service_id = $_GET['id'] ?? null;

$error_message = '';
$success_message = '';

if ($service_id) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $description = trim($_POST['description']);
        $amount = trim($_POST['amount']);
        $buying_date = trim($_POST['buying_date']);
        $registered_kilometers = trim($_POST['registered_kilometers']);
        $vehicle_id = trim($_POST['vehicle_id']);
        $delete_attachment = isset($_POST['delete_attachment']);
        $attachment_path = null;

        $part_names = $_POST['part_name'] ?? [];
        $part_numbers = $_POST['part_number'] ?? [];
        $part_ids = $_POST['part_id'] ?? [];  // Existing part IDs
        $parts_to_delete = explode(',', $_POST['parts_to_delete'] ?? ''); // Parts to delete

        // Validate inputs
        if (empty($description) || empty($buying_date)) {
            $error_message = "Inserisci tutti i campi obbligatori.";
        } else {
            // Start transaction
            $link->begin_transaction();
            try {
                // Update the service details
                $sql = "UPDATE vehicle_services 
                        SET description = ?, amount = ?, buying_date = ?, registered_kilometers = ? 
                        WHERE id = ? AND user_id = ?";
                if ($stmt = $link->prepare($sql)) {
                    $stmt->bind_param("sssiii", $description, $amount, $buying_date, $registered_kilometers, $service_id, $user_id);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    throw new Exception("Errore nella preparazione della query per l'aggiornamento della manutenzione: " . $link->error);
                }

                // Handle file upload
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
                            throw new Exception('Errore durante il caricamento del file.');
                        }
                    } else {
                        throw new Exception('Tipo di file non supportato. Solo .jpg, .jpeg, .png, .pdf sono permessi.');
                    }
                }

                // Check if the user requested to delete the attachment
                if ($delete_attachment) {
                    // Get the current attachment file path from the database
                    $sql = "SELECT attachment_path FROM vehicle_services WHERE id = ? AND user_id = ?";
                    if ($stmt = $link->prepare($sql)) {
                        $stmt->bind_param("ii", $service_id, $user_id);
                        $stmt->execute();
                        $stmt->bind_result($existing_attachment_path);
                        $stmt->fetch();
                        $stmt->close();

                        // Delete the file from the server
                        if ($existing_attachment_path && file_exists($existing_attachment_path)) {
                            unlink($existing_attachment_path);
                        }

                        // Update the database to remove the attachment
                        $sql = "UPDATE vehicle_services SET attachment_path = NULL WHERE id = ? AND user_id = ?";
                        if ($stmt = $link->prepare($sql)) {
                            $stmt->bind_param("ii", $service_id, $user_id);
                            $stmt->execute();
                            $stmt->close();
                        }
                    }
                }

                // Update the attachment path if a new one was uploaded
                if ($attachment_path) {
                    $sql = "UPDATE vehicle_services SET attachment_path = ? WHERE id = ? AND user_id = ?";
                    if ($stmt = $link->prepare($sql)) {
                        $stmt->bind_param("sii", $attachment_path, $service_id, $user_id);
                        $stmt->execute();
                        $stmt->close();
                    }
                }

                // Delete parts marked for deletion
                if (!empty($parts_to_delete)) {
                    $placeholders = implode(',', array_fill(0, count($parts_to_delete), '?'));
                    $sql = "DELETE FROM vehicle_service_parts WHERE id IN ($placeholders) AND user_id = ?";
                    if ($stmt = $link->prepare($sql)) {
                        $types = str_repeat('i', count($parts_to_delete)) . 'i';
                        $params = array_merge($parts_to_delete, [$user_id]);
                        $stmt->bind_param($types, ...$params);
                        $stmt->execute();
                        $stmt->close();
                    } else {
                        throw new Exception("Errore nella preparazione della query per la cancellazione delle parti: " . $link->error);
                    }
                }

                // Update existing parts or add new ones
                foreach ($part_names as $index => $part_name) {
                    $part_number = $part_numbers[$index] ?? '';
                    $part_id = $part_ids[$index] ?? null;

                    if ($part_id) {
                        // Update existing part
                        $sql = "UPDATE vehicle_service_parts 
                                SET part_name = ?, part_number = ? 
                                WHERE id = ? AND user_id = ?";
                        if ($stmt = $link->prepare($sql)) {
                            $stmt->bind_param("ssii", $part_name, $part_number, $part_id, $user_id);
                            $stmt->execute();
                            $stmt->close();
                        } else {
                            throw new Exception("Errore nella preparazione della query per l'aggiornamento delle parti: " . $link->error);
                        }
                    } else {
                        // Add new part
                        $sql = "INSERT INTO vehicle_service_parts (user_id, service_id, part_name, part_number) 
                                VALUES (?, ?, ?, ?)";
                        if ($stmt = $link->prepare($sql)) {
                            $stmt->bind_param("iiss", $user_id, $service_id, $part_name, $part_number);
                            $stmt->execute();
                            $stmt->close();
                        } else {
                            throw new Exception("Errore nella preparazione della query per l'inserimento delle parti: " . $link->error);
                        }
                    }
                }

                // Commit transaction
                $link->commit();

                $success_message = "Manutenzione e parti correlate modificate con successo.";
                header("refresh:3;url=dashboard.php");
            } catch (Exception $e) {
                // Rollback transaction on error
                $link->rollback();
                $error_message = $e->getMessage();
            }
        }
        $link->close();
    }
} else {
    $error_message = "Richiesta non valida a causa di service_id non fornito.";
}

// Display errors if any
if ($error_message) {
    echo "<div class='alert alert-danger'>$error_message</div>";
} elseif ($success_message) {
    echo "<div class='alert alert-success'>$success_message</div>";
}
?>
