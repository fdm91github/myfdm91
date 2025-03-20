<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['username'])) {
    header("location: ../login.php");
    exit;
}

$response = ["status" => "error", "message" => ""];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['id'];
    $wallet_id = isset($_POST['wallet_id']) ? intval($_POST['wallet_id']) : 0;
    $remove_user_id = isset($_POST['remove_user_id']) ? intval($_POST['remove_user_id']) : 0;

    if (empty($wallet_id) || empty($remove_user_id)) {
        $response['message'] = "Dati mancanti.";
    } else {
        // Check wallet ownership
        $sql = "SELECT shared_with FROM wallets WHERE id = ? AND user_id = ?";
        if ($stmt = $link->prepare($sql)) {
            $stmt->bind_param("ii", $wallet_id, $user_id);
            $stmt->execute();
            $stmt->bind_result($shared_with_json);
            if ($stmt->fetch()) {
                $stmt->close();
                $shared_with = json_decode($shared_with_json, true);
                if (!is_array($shared_with)) {
                    $shared_with = [];
                }
                // Remove the entry with matching id
                $found = false;
                foreach ($shared_with as $index => $entry) {
                    if (isset($entry['id']) && $entry['id'] == $remove_user_id) {
                        $found = true;
                        unset($shared_with[$index]);
                    }
                }
                if (!$found) {
                    $response['message'] = "Utente non trovato nella lista di condivisione.";
                } else {
                    // Re-index array and update
                    $shared_with = array_values($shared_with);
                    $new_shared_with_json = json_encode($shared_with);
                    $update_sql = "UPDATE wallets SET shared_with = ? WHERE id = ? AND user_id = ?";
                    if ($update_stmt = $link->prepare($update_sql)) {
                        $update_stmt->bind_param("sii", $new_shared_with_json, $wallet_id, $user_id);
                        if ($update_stmt->execute()) {
                            $response['status'] = "success";
                            $response['message'] = "Utente rimosso con successo.";
                        } else {
                            $response['message'] = "Errore durante l'aggiornamento.";
                        }
                        $update_stmt->close();
                    } else {
                        $response['message'] = "Errore durante la preparazione della query.";
                    }
                }
            } else {
                $response['message'] = "Portafoglio non trovato.";
            }
        } else {
            $response['message'] = "Errore durante la preparazione della query.";
        }
    }
    $link->close();
}

header('Content-Type: application/json');
echo json_encode($response);
?>
