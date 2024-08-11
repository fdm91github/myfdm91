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

    $sql = "DELETE FROM wallet_piggy_bank WHERE id = ? AND user_id = ?";

    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("ii", $id, $user_id);
        if ($stmt->execute()) {
            $response["status"] = "success";
            $response["message"] = "Piggy bank entry deleted successfully.";
        } else {
            $response["message"] = "Something went wrong. Please try again later.";
        }
        $stmt->close();
    } else {
        $response["message"] = "Something went wrong. Please try again later.";
    }

    $link->close();
}

header('Content-Type: application/json');
echo json_encode($response);
?>

