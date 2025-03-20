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
    $share_username = isset($_POST['share_username']) ? trim($_POST['share_username']) : '';

    if (empty($wallet_id) || empty($share_username)) {
        $response['message'] = "Dati mancanti.";
    } else {
        // Check if the target user exists in the users table
        $user_check_sql = "SELECT id, username FROM users WHERE username = ?";
        if ($user_check_stmt = $link->prepare($user_check_sql)) {
            $user_check_stmt->bind_param("s", $share_username);
            $user_check_stmt->execute();
            $user_check_stmt->store_result();
            if ($user_check_stmt->num_rows == 0) {
                $response['message'] = "Utente non trovato.";
                $user_check_stmt->close();
            } else {
                $user_check_stmt->bind_result($share_user_id, $db_username);
                $user_check_stmt->fetch();
                $user_check_stmt->close();
                // Impedisci di condividere con se stessi
                if ($share_user_id == $user_id) {
                    $response['message'] = "Non puoi condividere con te stesso.";
                } else {
                    // Verifica che il portafoglio appartenga all'utente
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
                            // Controlla se l'utente è già presente (confrontando gli id)
                            $alreadyShared = false;
                            foreach ($shared_with as $entry) {
                                if (isset($entry['id']) && $entry['id'] == $share_user_id) {
                                    $alreadyShared = true;
                                    break;
                                }
                            }
                            if ($alreadyShared) {
                                $response['message'] = "Hai già condiviso questo portafogli con l'utente.";
                            } else {
                                // Aggiungi il nuovo utente (associando id e username)
                                $shared_with[] = ['id' => $share_user_id, 'username' => $db_username];
                                $new_shared_with_json = json_encode($shared_with);
                                $update_sql = "UPDATE wallets SET shared_with = ? WHERE id = ? AND user_id = ?";
                                if ($update_stmt = $link->prepare($update_sql)) {
                                    $update_stmt->bind_param("sii", $new_shared_with_json, $wallet_id, $user_id);
                                    if ($update_stmt->execute()) {
                                        $response['status'] = "success";
                                        $response['message'] = "Portafoglio condiviso con successo!";
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
