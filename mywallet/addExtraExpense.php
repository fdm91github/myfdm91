<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("location: ../login.php");
    exit;
}

require_once '../config.php';

$response = ["status" => "error", "message" => ""];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['id'];
    $name = trim($_POST["name"]);
    $amount = trim($_POST["amount"]);
    $debit_date = trim($_POST["debit_date"]);

    // Validate inputs
    if (empty($name) || empty($amount) || empty($debit_date)) {
        $response["message"] = "Inserisci tutti i campi obbligatori.";
    } elseif (!is_numeric($amount) || $amount <= 0) {
        $response["message"] = "Inserisci un importo valido.";
    } else {
        // Check for duplicate entry
        $check_sql = "SELECT id FROM extra_expenses WHERE user_id = ? AND name = ? AND amount = ? AND debit_date = ?";
        if ($check_stmt = $link->prepare($check_sql)) {
            $check_stmt->bind_param("isds", $user_id, $name, $amount, $debit_date);
            $check_stmt->execute();
            $check_stmt->store_result();
            
            if ($check_stmt->num_rows > 0) {
                $response["message"] = "La spesa che stai provando ad aggiungere esiste già.";
            } else {
                // Insert new entry if no duplicate found
                $sql = "INSERT INTO extra_expenses (user_id, name, amount, debit_date) VALUES (?, ?, ?, ?)";
                if ($stmt = $link->prepare($sql)) {
                    $stmt->bind_param("isds", $user_id, $name, $amount, $debit_date);
                    if ($stmt->execute()) {
                        $response["status"] = "success";
                        $response["message"] = "Spesa aggiunta con successo!";
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

