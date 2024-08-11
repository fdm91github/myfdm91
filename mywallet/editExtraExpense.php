<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("location: ../login.php");
    exit;
}

require_once '../config.php';

$user_id = $_SESSION['id'];
$expense_id = $_GET['id'] ?? null;

$error_message = '';
$success_message = '';

if ($expense_id) {
    // Fetch existing extra expense details
    $sql = "SELECT name, amount, debit_date FROM wallet_extra_expenses WHERE id = ? AND user_id = ?";
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("ii", $expense_id, $user_id);
        $stmt->execute();
        $stmt->bind_result($name, $amount, $debit_date);
        $stmt->fetch();
        $stmt->close();
    } else {
        $error_message = "Qualcosa è andato storto. Riprova più tardi.";
        exit;
    }
} else {
    $error_message = "Richiesta non valida a causa di extra_expense_id non fornito.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $amount = trim($_POST['amount']);
    $debit_date = trim($_POST['debit_date']);

    // Validate inputs
    if (empty($name) || empty($amount) || empty($debit_date)) {
        $error_message = "Inserisci tutti i campi obbligatori.";
    } elseif (!is_numeric($amount) || $amount <= 0) {
        $error_message = "Inserisci un importo valido.";
    } else {
        $sql = "UPDATE wallet_extra_expenses SET name = ?, amount = ?, debit_date = ? WHERE id = ? AND user_id = ?";
        if ($stmt = $link->prepare($sql)) {
            $stmt->bind_param("sdssi", $name, $amount, $debit_date, $expense_id, $user_id);
            if ($stmt->execute()) {
                $success_message = "Spesa modificata con successo. Reindirizzamento alla dashboard...";
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