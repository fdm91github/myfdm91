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
        $sql_parts = "DELETE FROM vehicle_service_parts WHERE service_id = ? AND user_id = ?";
        if ($stmt_parts = $link->prepare($sql_parts)) {
            $stmt_parts->bind_param("ii", $id, $user_id);
            $stmt_parts->execute();
            $stmt_parts->close();
        } else {
            throw new Exception("Errore nella preparazione della query per l'eliminazione delle parti.");
        }

        // Delete the service
        $sql_service = "DELETE FROM vehicle_services WHERE id = ? AND user_id = ?";
        if ($stmt_service = $link->prepare($sql_service)) {
            $stmt_service->bind_param("ii", $id, $user_id);
            $stmt_service->execute();
            $stmt_service->close();
        } else {
            throw new Exception("Errore nella preparazione della query per l'eliminazione della manutenzione.");
        }

        // Commit transaction
        $link->commit();

        $response["status"] = "success";
        $response["message"] = "Manutenzione e parti correlate eliminate con successo.";
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
