<?php
session_start();
require_once 'config.php';
if (!isset($_SESSION['id'])) { http_response_code(401); exit; }

$userId = (int)$_SESSION['id'];

/*
Inserisce in notification_reads tutte le notifications mancanti per questo utente.
La subquery seleziona solo le notifiche NON già lette (NOT EXISTS).
*/
$sql = "
  INSERT INTO notification_reads (user_id, notification_id)
  SELECT ?, n.id
  FROM notifications n
  WHERE NOT EXISTS (
    SELECT 1 FROM notification_reads r
    WHERE r.user_id = ? AND r.notification_id = n.id
  )
";
if ($stmt = $link->prepare($sql)) {
  $stmt->bind_param("ii", $userId, $userId);
  $stmt->execute();
  $stmt->close();
}

echo 'OK';

