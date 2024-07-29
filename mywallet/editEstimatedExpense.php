<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("location: ../login.php");
    exit;
}

require_once 'config.php';

$user_id = $_SESSION['id'];
$expense_id = $_GET['id'] ?? null;

$error_message = '';
$success_message = '';

if ($expense_id) {
    // Carico le spese stimate esistenti
    $sql = "SELECT name, amount, start_month, start_year, end_month, end_year, undetermined, debit_date, billing_frequency
            FROM estimated_expenses
            WHERE id = ? AND user_id = ?";
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("ii", $expense_id, $user_id);
        $stmt->execute();
        $stmt->bind_result($name, $amount, $start_month, $start_year, $end_month, $end_year, $undetermined, $debit_date, $billing_frequency);
        $stmt->fetch();
        $stmt->close();
    } else {
        $error_message = "Qualcosa è andato storto. Riprova più tardi.";
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $amount = trim($_POST['amount']);
    $start_month = trim($_POST['start_month']);
	$start_year = trim($_POST['start_year']);
    $end_month = trim($_POST['end_month']);
	$end_year = trim($_POST['end_year']);
    $undetermined = isset($_POST['undetermined']) ? 1 : 0;
    $debit_date = trim($_POST['debit_date']);
    $billing_frequency = trim($_POST['billing_frequency']);

    // Convalido l'input
    if (empty($name) || empty($amount) || empty($start_month) || empty($start_year) || empty($debit_date) || empty($billing_frequency)) {
        $error_message = "Inserisci tutti i campi obbligatori.";
    } elseif (!is_numeric($amount) || $amount <= 0) {
        $error_message = "Inserisci un importo valido.";
    } elseif ($undetermined === 0 && (empty($end_month) || empty($end_year))) {
        $error_message = "Fornisci una data di fine oppure marca la spesa come indeterminata.";
    } else {
        // Imposta end_month e end_year a NULL se la spesa è indeterminata
        $end_month = $undetermined ? NULL : $end_month;
        $end_year = $undetermined ? NULL : $end_year;

        $sql = "UPDATE estimated_expenses
                SET name = ?, amount = ?, start_month = ?, start_year = ?, end_month = ?, end_year = ?, undetermined = ?, debit_date = ?, billing_frequency = ?
                WHERE id = ? AND user_id = ?";

        if ($stmt = $link->prepare($sql)) {
            $stmt->bind_param("sdssssiiiii", $name, $amount, $start_month, $start_year, $end_month, $end_year, $undetermined, $debit_date, $billing_frequency, $expense_id, $user_id);
            if ($stmt->execute()) {
                $success_message = "Spesa aggiunta correttamente. Reindirizzamento alla dashboard...";
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