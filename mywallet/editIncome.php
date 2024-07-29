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
    $id = $_POST["id"];
	$name = $_POST["name"];
    $amount = trim($_POST["amount"]);
    $added_date = trim($_POST["added_date"]);

    // Validate inputs
    if (empty($amount) || empty($added_date)) {
        $response["message"] = "Inserisci tutti i campi obbligatori.";
    } elseif (!is_numeric($amount)) {
        $response["message"] = "Inserisci un valore valido.";
    } else {
        $sql = "UPDATE incomes SET name = ?, amount = ?, added_date = ? WHERE id = ? AND user_id = ?";

        if ($stmt = $link->prepare($sql)) {
            $stmt->bind_param("sdsii", $name, $amount, $added_date, $id, $user_id);
            if ($stmt->execute()) {
                $response["status"] = "success";
                $response["message"] = "Voce aggiornata.";
            } else {
                $response["message"] = "Qualcosa è andato storto. Riprova più tardi.";
            }
            $stmt->close();
        } else {
            $response["message"] = "Qualcosa è andato storto. Riprova più tardi.";
        }
    }
    $link->close();
}

header('Content-Type: application/json');
echo json_encode($response);
?>

