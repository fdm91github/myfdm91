<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("location: ../login.php");
    exit;
}

require_once '../config.php';

$response = ["status" => "error", "message" => ""];

if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $user_id = $_SESSION['id'];

    // Start transaction
    $link->begin_transaction();

    try {
        // Delete related parts
        $sql_parts = "DELETE FROM wallet_data_parts WHERE wallet_data_id = ? AND user_id = ?";
        if ($stmt_parts = $link->prepare($sql_parts)) {
            $stmt_parts->bind_param("ii", $id, $user_id);
            $stmt_parts->execute();
            $stmt_parts->close();
        } else {
            throw new Exception("Errore nella preparazione della query per l'eliminazione dei prodotti.");
        }

        // Delete the wallet_data
        $sql_service = "DELETE FROM wallet_data WHERE id = ? AND user_id = ?";
        if ($stmt_wallet_data = $link->prepare($sql_service)) {
            $stmt_wallet_data->bind_param("ii", $id, $user_id);
            $stmt_wallet_data->execute();
            $stmt_wallet_data->close();
        } else {
            throw new Exception("Errore nella preparazione della query per l'eliminazione della spesa.");
        }

        // Commit transaction
        $link->commit();

        $response["status"] = "success";
        $response["message"] = "Spesa e prodotti correlati eliminate con successo.";
    } catch (Exception $e) {
        // Rollback transaction if any query fails
        $link->rollback();

        $response["message"] = $e->getMessage();
    }

    $link->close();
} else {
    $response["message"] = "Invalid request. No service ID provided.";
}

header('Content-Type: application/json');
echo json_encode($response);
?>
