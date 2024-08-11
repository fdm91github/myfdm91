<?php
session_start();

$response = ["status" => "error", "message" => ""];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once '../config.php';

    $user_id = $_SESSION['id'];
    $service_id = $_POST['service_id'] ?? null;
    $part_names = $_POST['part_name'] ?? [];
    $part_numbers = $_POST['part_number'] ?? [];

    if (empty($service_id)) {
        $response["message"] = "Service ID is missing.";
    } elseif (empty($part_names) || empty($part_numbers)) {
        $response["message"] = "Part names or part numbers are missing.";
    } else {
        $stmt = $link->prepare("INSERT INTO vehicle_service_parts (user_id, service_id, part_name, part_number) VALUES (?, ?, ?, ?)");

        if ($stmt) {
            foreach ($part_names as $index => $part_name) {
                $part_number = $part_numbers[$index] ?? '';

                if (empty($part_name) || empty($part_number)) {
                    continue;  // Skip empty parts
                }

                $stmt->bind_param("iiss", $user_id, $service_id, $part_name, $part_number);
                if (!$stmt->execute()) {
                    $response["message"] = "Errore nell'inserimento della parte: " . $part_name;
                    echo json_encode($response);
                    exit;
                }
            }
            $response["status"] = "success";
            $response["message"] = "Parti aggiunte con successo!";
        } else {
            $response["message"] = "Errore nella preparazione della query.";
        }
        $stmt->close();
    }
    $link->close();
}