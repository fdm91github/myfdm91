<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['username'])) {
    header("location: ../login.php");
    exit;
}

$response = ["status" => "error", "message" => ""];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $wallet_id = isset($_POST['wallet_id']) ? intval($_POST['wallet_id']) : 0;
    $current_user_id = $_SESSION['id'];
    
    if (empty($wallet_id)) {
        $response['message'] = "Dati mancanti.";
    } else {
        // Get the current shared_with data for the wallet
        $sql = "SELECT shared_with FROM wallets WHERE id = ?";
        if ($stmt = $link->prepare($sql)) {
            $stmt->bind_param("i", $wallet_id);
            $stmt->execute();
            $stmt->bind_result($shared_with_json);
            if ($stmt->fetch()) {
                $stmt->close();
                $shared_with = json_decode($shared_with_json, true);
                if (!is_array($shared_with)) {
                    $shared_with = [];
                }
                // Remove any entry corresponding to the current user
                $found = false;
                foreach ($shared_with as $index => $entry) {
                    if (isset($entry['id']) && $entry['id'] == $current_user_id) {
                        $found = true;
                        unset($shared_with[$index]);
                    }
                }
                if (!$found) {
                    $response['message'] = "Non risulti condiviso con questo portafoglio.";
                } else {
                    // Re-index array and update the wallet record
                    $shared_with = array_values($shared_with);
                    $new_shared_with_json = json_encode($shared_with);
                    $update_sql = "UPDATE wallets SET shared_with = ? WHERE id = ?";
                    if ($update_stmt = $link->prepare($update_sql)) {
                        $update_stmt->bind_param("si", $new_shared_with_json, $wallet_id);
                        if ($update_stmt->execute()) {
                            $response['status'] = "success";
                            $response['message'] = "Condivisione rimossa con successo.";
                        } else {
                            $response['message'] = "Errore durante l'aggiornamento.";
                        }
                        $update_stmt->close();
                    } else {
                        $response['message'] = "Errore nella preparazione della query.";
                    }
                }
            } else {
                $response['message'] = "Portafoglio non trovato.";
            }
        } else {
            $response['message'] = "Errore nella preparazione della query.";
        }
    }
    $link->close();
}

header('Content-Type: application/json');
echo json_encode($response);
?>
