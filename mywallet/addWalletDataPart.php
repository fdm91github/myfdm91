<?php
session_start();

$response = ["status" => "error", "message" => ""];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once '../config.php';

    $user_id = $_SESSION['id'];
    $wallet_data_id = $_POST['wallet_data_id'] ?? null;
    $part_names = $_POST['part_name'] ?? [];
    $part_costs = $_POST['part_cost'] ?? [];

    if (empty($wallet_data_id)) {
        $response["message"] = "Wallet Data ID is missing.";
    } elseif (empty($part_names) || empty($part_costs)) {
        $response["message"] = "Part names or part costs are missing.";
    } else {
        $stmt = $link->prepare("INSERT INTO wallet_data_parts (user_id, wallet_data_id, part_name, part_cost) VALUES (?, ?, ?, ?)");

        if ($stmt) {
            foreach ($part_names as $index => $part_name) {
                $part_cost = $part_costs[$index] ?? '';

                if (empty($part_name) || empty($part_cost)) {
                    continue;
                }

                $stmt->bind_param("iiss", $user_id, $wallet_data_id, $part_name, $part_cost);
                if (!$stmt->execute()) {
                    $response["message"] = "Errore nell'inserimento del prodotto: " . $part_name;
                    echo json_encode($response);
                    exit;
                }
            }
            $response["status"] = "success";
            $response["message"] = "Prodotti aggiunti con successo!";
        } else {
            $response["message"] = "Errore nella preparazione della query.";
        }
        $stmt->close();
    }
    $link->close();
}