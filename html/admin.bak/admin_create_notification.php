<?php
session_start();
require_once '../config.php';

if (($_SESSION['username'] ?? '') !== 'fdellamorte') {
  http_response_code(403);
  exit('Forbidden');
}

$title = trim($_POST['title'] ?? '');
$body  = trim($_POST['body'] ?? '');

if ($title === '' || $body === '') {
  http_response_code(422);
  exit('Titolo e corpo obbligatori');
}

$stmt = $link->prepare("INSERT INTO notifications (title, body) VALUES (?, ?)");
$stmt->bind_param("ss", $title, $body);
$stmt->execute();
$stmt->close();

echo 'OK';

