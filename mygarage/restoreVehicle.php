<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("location: ../login.php");
    exit;
}

require_once '../config.php';

$error_message = '';
$success_message = '';

if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $user_id = $_SESSION['id'];

    $sql = "UPDATE vehicles SET deleted_at = NULL WHERE id = ? AND user_id = ?";
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("ii", $id, $user_id);

        if ($stmt->execute()) {
            $success_message = "Veicolo ripristinato con successo.";
            header("refresh:3; url=dashboard.php");
        } else {
            $error_message = "Qualcosa è andato storto. Riprova più tardi.";
        }
        $stmt->close();
    } else {
        $error_message = "Qualcosa è andato storto. Riprova più tardi.";
    }
    $link->close();
} else {
    $error_message = "Invalid request. No expense ID provided.";
}
?>