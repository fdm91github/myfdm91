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

/*
 Inserisce in notification_reads TUTTE le notifiche mancanti per questo utente,
 marcandole come lette ORA. Evitiamo duplicati con NOT EXISTS.
 Nota: se la tabella ha PRIMARY KEY (user_id, notification_id), è già “idempotente”.
*/
$sql = "
  INSERT INTO notification_reads (user_id, notification_id, read_at)
  SELECT ?, n.id, NOW()
  FROM notifications n
  WHERE NOT EXISTS (
    SELECT 1
    FROM notification_reads r
    WHERE r.user_id = ? AND r.notification_id = n.id
  )
";
$inserted = 0;

if ($stmt = $link->prepare($sql)) {
    $stmt->bind_param("ii", $userId, $userId);
    if ($stmt->execute()) {
        $inserted = $stmt->affected_rows; // quante sono state marcate ora
        echo json_encode(['ok' => true, 'marked' => $inserted]);
        $stmt->close();
        exit;
    }
    $stmt->close();
}

http_response_code(500);
echo json_encode(['ok' => false, 'err' => 'db_error']);
