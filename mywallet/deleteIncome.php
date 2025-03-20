<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("location: ../login.php");
    exit;
}

require_once '../config.php';

$response = ['status' => '', 'message' => ''];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $income_id = trim($_POST['income_id']);
    $user_id = $_SESSION['id'];

    if (empty($income_id)) {
        $response['status'] = 'error';
        $response['message'] = 'ID entrata mancante.';
    } else {
        $sql = "DELETE FROM wallet_incomes WHERE id = ? AND user_id = ?";
        if ($stmt = $link->prepare($sql)) {
            $stmt->bind_param("ii", $income_id, $user_id);

            if ($stmt->execute()) {
                $response['status'] = 'success';
                $response['message'] = 'Entrata eliminata correttamente.';
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Qualcosa è andato storto. Riprova più tardi.';
            }
            $stmt->close();
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Qualcosa è andato storto. Riprova più tardi.';
        }
    }
    $link->close();
}

echo json_encode($response);
?>
