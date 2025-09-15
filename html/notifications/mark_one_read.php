<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'err' => 'unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'err' => 'method_not_allowed']);
    exit;
}

$userId = (int)$_SESSION['id'];
$notifId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($notifId <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'err' => 'bad_request']);
    exit;
}

// (Opzionale) verifica che la notifica esista
$exists = false;
if ($stmt = $link->prepare("SELECT 1 FROM notifications WHERE id = ?")) {
    $stmt->bind_param("i", $notifId);
    $stmt->execute();
    $stmt->store_result();
    $exists = ($stmt->num_rows === 1);
    $stmt->close();
}
if (!$exists) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'err' => 'not_found']);
    exit;
}

// Inserisci SOLO quella notifica (ignora se già presente)
$sql = "INSERT IGNORE INTO notification_reads (user_id, notification_id, read_at)
        VALUES (?, ?, NOW())";
if ($stmt = $link->prepare($sql)) {
    $stmt->bind_param("ii", $userId, $notifId);
    if ($stmt->execute()) {
        echo json_encode(['ok' => true]);
        exit;
    }
    $stmt->close();
}

http_response_code(500);
echo json_encode(['ok' => false, 'err' => 'db_error']);
