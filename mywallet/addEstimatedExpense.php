<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("location: ../login.php");
    exit;
}

require_once 'config.php';

$response = ["status" => "error", "message" => ""];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['id'];
    $name = trim($_POST["name"]);
    $amount = trim($_POST["amount"]);
    $start_month = trim($_POST["start_month"]);
    $start_year = trim($_POST["start_year"]);
    $end_month = trim($_POST["end_month"]);
    $end_year = trim($_POST["end_year"]);
    $undetermined = isset($_POST["undetermined"]) ? 1 : 0;
    $debit_date = trim($_POST["debit_date"]);
    $billing_frequency = trim($_POST["billing_frequency"]);

    // Convalido i dati
    if (empty($name) || empty($amount) || empty($start_month) || empty($start_year) || empty($debit_date) || empty($billing_frequency)) {
        $response["message"] = "Inserisci tutti i campi obbligatori.";
    } elseif (!is_numeric($amount) || $amount <= 0) {
        $response["message"] = "Inserisci un valore valido.";
    } elseif ($undetermined == 0 && (empty($end_month) || empty($end_year))) {
        $response["message"] = "Fornisci una data di fine oppure marca la spesa come indeterminata.";
    } else {
        // Imposta end_month e end_year a NULL se la spesa è indeterminata
        $end_month = $undetermined ? NULL : $end_month;
        $end_year = $undetermined ? NULL : $end_year;

        // Controlla se esiste già una spesa identica
        $check_sql = "SELECT id FROM estimated_expenses WHERE user_id = ? AND name = ? AND amount = ? AND start_month = ? AND start_year = ? AND (end_month = ? OR end_month IS NULL) AND (end_year = ? OR end_year IS NULL) AND undetermined = ? AND debit_date = ? AND billing_frequency = ?";
        if ($check_stmt = $link->prepare($check_sql)) {
            $check_stmt->bind_param("isdiiiiiii", $user_id, $name, $amount, $start_month, $start_year, $end_month, $end_year, $undetermined, $debit_date, $billing_frequency);
            $check_stmt->execute();
            $check_stmt->store_result();
            
            if ($check_stmt->num_rows > 0) {
                $response["message"] = "La spesa che stai provando ad aggiungere esiste già.";
            } else {
                $sql = "INSERT INTO estimated_expenses (user_id, name, amount, start_month, start_year, end_month, end_year, undetermined, debit_date, billing_frequency) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                if ($stmt = $link->prepare($sql)) {
                    $stmt->bind_param("isdiiiiiii", $user_id, $name, $amount, $start_month, $start_year, $end_month, $end_year, $undetermined, $debit_date, $billing_frequency);
                    if ($stmt->execute()) {
                        $response["status"] = "success";
                        $response["message"] = "Spesa aggiunta con successo.";
                    } else {
                        $response["message"] = "Qualcosa è andato storto. Riprova più tardi.";
                    }
                    $stmt->close();
                } else {
                    $response["message"] = "Qualcosa è andato storto. Riprova più tardi.";
                }
            }
            $check_stmt->close();
        } else {
            $response["message"] = "Qualcosa è andato storto. Riprova più tardi.";
        }
    }
    $link->close();
}

header('Content-Type: application/json');
echo json_encode($response);
?>
