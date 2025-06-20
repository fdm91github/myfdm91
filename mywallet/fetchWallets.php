<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("location: ../login.php");
    exit;
}

require_once '../config.php';

if (!isset($_SESSION['id'])) {
  echo json_encode(['wallets'=>[]]);
  exit;
}
$user_id = $_SESSION['id'];

// Prendo solo i portafogli attivi
$sql = "SELECT id, description 
        FROM wallets 
        WHERE user_id = ? 
        AND deleted_at IS NULL
        ORDER BY description ASC";
$stmt = $link->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

$wallets = [];
while ($row = $res->fetch_assoc()) {
    // “name” combacia col JS che ti aspetta response.wallets[i].name
    $wallets[] = [
      'id'   => $row['id'],
      'name' => $row['description']
    ];
}

echo json_encode(['wallets' => $wallets]);
?>