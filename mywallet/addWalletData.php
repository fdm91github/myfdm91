<?php
session_start();

$response = ["status" => "error", "message" => "", "wallet_data_id" => null];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once '../config.php';

    $response = ["status" => "error", "message" => "", "wallet_data_id" => null];
    $user_id = $_SESSION['id'];
    $wallet_id = trim($_POST['wallet_id']);
    $description = trim($_POST["description"]);
    $amount = trim($_POST["amount"]);
    $buying_date = trim($_POST["buying_date"]);

    // Parts data
    $part_names = $_POST['part_name'] ?? [];
    $part_costs = $_POST['part_cost'] ?? [];

    // Validate inputs
    if (empty($wallet_id) || empty($description) || empty($buying_date)) {
        $response["message"] = "Inserisci tutti i campi obbligatori.";
    } else {
        // Begin transaction
        $link->begin_transaction();
        try {
            // Insert the main wallet_data record
            $sql = "INSERT INTO wallet_data (user_id, wallet_id, description, amount, buying_date) 
                    VALUES (?, ?, ?, ?, ?)";
            if ($stmt = $link->prepare($sql)) {
                $stmt->bind_param("iisss", $user_id, $wallet_id, $description, $amount, $buying_date);
                if ($stmt->execute()) {
                    $response["status"] = "success";
                    $response["message"] = "Spesa aggiunta con successo!";
                    $response["wallet_data_id"] = $link->insert_id;
                } else {
                    throw new Exception("Errore nell'inserimento della spesa: " . $stmt->error);
                }
                $stmt->close();
            } else {
                throw new Exception("Errore nella preparazione della query: " . $link->error);
            }

            // Insert parts if provided
            foreach ($part_names as $index => $part_name) {
                $part_cost = $part_costs[$index] ?? '';

                $sql = "INSERT INTO wallet_data_parts (user_id, wallet_data_id, part_name, part_cost) 
                        VALUES (?, ?, ?, ?)";
                if ($stmt = $link->prepare($sql)) {
                    $stmt->bind_param("iiss", $user_id, $response["wallet_data_id"], $part_name, $part_cost);
                    if (!$stmt->execute()) {
                        throw new Exception("Errore nell'inserimento dei prodotti: " . $stmt->error);
                    }
                    $stmt->close();
                } else {
                    throw new Exception("Errore nella preparazione della query per i prodotti: " . $link->error);
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
