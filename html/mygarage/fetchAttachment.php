<?php
require_once '../config.php';

$service_id = $_GET['service_id'] ?? null;
$response = ['attachment' => null];

if ($service_id) {
    $sql = "SELECT attachment_path FROM vehicle_services WHERE id = ?";
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("i", $service_id);
        $stmt->execute();
        $stmt->bind_result($attachment_path);
        $stmt->fetch();
        if ($attachment_path) {
            $response['attachment'] = [
                'path' => $attachment_path,
                'name' => basename($attachment_path)
            ];
        }
        $stmt->close();
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>
