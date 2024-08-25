<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("location: ../login.php");
    exit;
}

require_once '../config.php';

$user_id = $_SESSION['id'];
$kilometers_id = $_GET['id'] ?? null;

$error_message = '';
$success_message = '';

if ($kilometers_id) {
    // Fetch existing kilometers details
    $sql = "SELECT kilometers, date, vehicle_id 
            FROM vehicle_km_registered 
            WHERE id = ? AND user_id = ?";
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("ii", $kilometers_id, $user_id);
        $stmt->execute();
        $stmt->bind_result($amount, $date, $vehicle_id);
        $stmt->fetch();
        $stmt->close();
    } else {
        $error_message = "Qualcosa è andato storto. Riprova più tardi.";
        exit;
    }
} else {
    $error_message = "Richiesta non valida a causa di kilometers_id non fornito.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = trim($_POST['amount']);
    $date = trim($_POST['date']);
    $vehicle_id = trim($_POST['vehicle_id']);  // Ensure the vehicle_id is passed from the form

    // Validate inputs
    if (empty($amount) || empty($date)) {
        $error_message = "Inserisci tutti i campi obbligatori.";
    } else {
        $sql = "UPDATE vehicle_km_registered 
                SET kilometers = ?, date = ? 
                WHERE id = ? AND user_id = ?";
        if ($stmt = $link->prepare($sql)) {
            $stmt->bind_param("ssii", $amount, $date, $kilometers_id, $user_id);
            if ($stmt->execute()) {
                $success_message = "Percorrenza modificata con successo.";
                header("refresh:3;url=dashboard.php");
            } else {
                $error_message = "Qualcosa è andato storto. Riprova più tardi.";
            }
            $stmt->close();
        } else {
            $error_message = "Qualcosa è andato storto. Riprova più tardi.";
        }
    }
    $link->close();
}
?>
