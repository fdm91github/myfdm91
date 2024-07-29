<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("location: ../login.php");
    exit;
}

require_once '../config.php';

$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
	$amount = trim($_POST['amount']);
    $date = trim($_POST['added_date']);
    $user_id = $_SESSION['id'];

    // Validate the amount and date
    if (empty($name) || empty($amount) || empty($date)) {
        $error_message = "Descrizione, totale e data sono campi obbligatori.";
    } elseif (!is_numeric($amount)) {
        $error_message = "Inserisci un importo valido.";
    } else {
        $sql = "INSERT INTO piggy_bank (name, user_id, amount, added_date) VALUES (?, ?, ?, ?)";

        if ($stmt = $link->prepare($sql)) {
            $stmt->bind_param("sids", $name, $user_id, $amount, $date);

            if ($stmt->execute()) {
                $success_message = "Cifra aggiunta al salvadanaio con successo. Reindirizzamento alla dashboard...";
                header("refresh:3; url=dashboard.php");
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