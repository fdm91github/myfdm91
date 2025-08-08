<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("location: ../login.php");
    exit;
}

require_once '../config.php';

$user_id = $_SESSION['id'];
$wallet_data_id = $_GET['id'] ?? null;

$error_message = '';
$success_message = '';

if ($wallet_data_id) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $description = trim($_POST['description']);
        $amount = trim($_POST['amount']);
        $buying_date = trim($_POST['buying_date']);
        $wallet_id = trim($_POST['wallet_id']);

        $part_names = $_POST['part_name'] ?? [];
        $part_costs = $_POST['part_cost'] ?? [];
        $part_ids = $_POST['part_id'] ?? [];  // Existing part IDs
        $parts_to_delete = explode(',', $_POST['parts_to_delete'] ?? ''); // Parts to delete

        // Validate inputs
        if (empty($description) || empty($buying_date)) {
            $error_message = "Inserisci tutti i campi obbligatori.";
        } else {
            // Start transaction
            $link->begin_transaction();
            try {
                // Update the wallet_data details
                $sql = "UPDATE wallet_data 
                        SET wallet_id = ?, description = ?, amount = ?, buying_date = ?
						WHERE id = ? AND user_id = ?";
                if ($stmt = $link->prepare($sql)) {
                    $stmt->bind_param("isssii", $wallet_id, $description, $amount, $buying_date, $wallet_data_id, $user_id);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    throw new Exception("Errore nella preparazione della query per l'aggiornamento della spesa: " . $link->error);
                }

                // Delete parts marked for deletion
                if (!empty($parts_to_delete)) {
                    $placeholders = implode(',', array_fill(0, count($parts_to_delete), '?'));
                    $sql = "DELETE FROM wallet_data_parts
							WHERE id IN ($placeholders)
							AND user_id = ?";
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
					$part_cost = $part_costs[$index] ?? '';
					$part_id = $part_ids[$index] ?? null;

					if ($part_id) {
						// Update existing part
						$sql = "UPDATE wallet_data_parts
								SET part_name = ?, part_cost = ? 
								WHERE id = ?
								AND user_id = ?";
						if ($stmt = $link->prepare($sql)) {
							$stmt->bind_param("ssii", $part_name, $part_cost, $part_id, $user_id);
							$stmt->execute();
							$stmt->close();
						} else {
							throw new Exception("Errore nella preparazione della query per l'aggiornamento delle parti: " . $link->error);
						}
					} else {
						// Check if part already exists to avoid duplication
						$sql_check = "SELECT id FROM wallet_data_parts WHERE user_id = ? AND wallet_data_id = ? AND part_name = ? AND part_cost = ?";
						if ($stmt_check = $link->prepare($sql_check)) {
							$stmt_check->bind_param("iiss", $user_id, $wallet_data_id, $part_name, $part_cost);
							$stmt_check->execute();
							$stmt_check->store_result();
							if ($stmt_check->num_rows == 0) { // Part doesn't exist, insert it
								$sql = "INSERT INTO wallet_data_parts (user_id, wallet_data_id, part_name, part_cost) 
										VALUES (?, ?, ?, ?)";
								if ($stmt = $link->prepare($sql)) {
									$stmt->bind_param("iiss", $user_id, $wallet_data_id, $part_name, $part_cost);
									$stmt->execute();
									$stmt->close();
								} else {
									throw new Exception("Errore nella preparazione della query per l'inserimento delle parti: " . $link->error);
								}
							}
							$stmt_check->close();
						}
					}
				}

                // Commit transaction
                $link->commit();

                $success_message = "Spesa e prodotti correlati modificati con successo.";
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
    $error_message = "Richiesta non valida a causa di wallet_data_id non fornito.";
}

// Display errors if any
if ($error_message) {
    echo "<div class='alert alert-danger'>$error_message</div>";
} elseif ($success_message) {
    echo "<div class='alert alert-success'>$success_message</div>";
}
?>
