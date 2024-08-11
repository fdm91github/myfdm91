<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("location: ../login.php");
    exit;
}

require_once '../config.php';

$response = ["status" => "error", "parts" => []];

if (isset($_GET['service_id'])) {
    $service_id = $_GET['service_id'];
    $user_id = $_SESSION['id'];

    $sql = "SELECT id, part_name, part_number 
            FROM vehicle_service_parts 
            WHERE service_id = ? AND user_id = ?";
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("ii", $service_id, $user_id);
        $stmt->execute();
        $stmt->bind_result($part_id, $part_name, $part_number);
        while ($stmt->fetch()) {
            $response['parts'][] = ['id' => $part_id, 'part_name' => $part_name, 'part_number' => $part_number];
        }
        $stmt->close();
        $response["status"] = "success";
    } else {
        $response["message"] = "Errore nella preparazione della query per il recupero delle parti.";
    }
    $link->close();
}

header('Content-Type: application/json');
echo json_encode($response);
?>
