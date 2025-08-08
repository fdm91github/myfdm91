<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("location: ../login.php");
    exit;
}

require_once '../config.php';

$user_id = $_SESSION['id'];
$vehicle_id = $_GET['id'] ?? null;

$error_message = '';
$success_message = '';

if ($vehicle_id) {
    // Fetch existing vehicle details
    $sql = "SELECT description, buying_date, registration_date, plate_number, chassis_number, tax_month 
            FROM vehicles 
            WHERE id = ? AND user_id = ?";
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("ii", $vehicle_id, $user_id);
        $stmt->execute();
        $stmt->bind_result($description, $buying_date, $registration_date, $plate_number, $chassis_number, $tax_month);
        $stmt->fetch();
        $stmt->close();
    } else {
        $error_message = "Qualcosa è andato storto. Riprova più tardi.";
        exit;
    }
} else {
    $error_message = "Richiesta non valida a causa di vehicle_id non fornito.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $description = trim($_POST['description']);
    $buying_date = trim($_POST['buying_date']);
    $registration_date = trim($_POST['registration_date']);
    $plate_number = trim($_POST['plate_number']);
    $chassis_number = trim($_POST['chassis_number']);
    $tax_month = trim($_POST['tax_month']);

    // Validate inputs
    if (empty($description) || empty($buying_date) || empty($registration_date) || empty($plate_number) || empty($chassis_number) || empty($tax_month)) {
        $error_message = "Inserisci tutti i campi obbligatori.";
    } else {
        $sql = "UPDATE vehicles 
                SET description = ?, buying_date = ?, registration_date = ?, plate_number = ?, chassis_number = ?, tax_month = ? 
                WHERE id = ? AND user_id = ?";
        if ($stmt = $link->prepare($sql)) {
            $stmt->bind_param("ssssssii", $description, $buying_date, $registration_date, $plate_number, $chassis_number, $tax_month, $vehicle_id, $user_id);
            if ($stmt->execute()) {
                $success_message = "Veicolo modificato con successo. Reindirizzamento alla dashboard...";
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
