<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("location: ../login.php");
    exit;
}

require_once '../config.php';

$error_message = '';
$success_message = '';

if (isset($_POST['id'])) {
    $wallet_id = $_POST['id'];
    $user_id = $_SESSION['id'];

    // Start transaction
    $link->begin_transaction();

    try {
        // 1. Delete parts associated with walletData entries for this wallet.
        //    This uses a JOIN so that we remove only the parts for walletData rows belonging to this wallet.
        $sql_parts = "DELETE wdp
                      FROM wallet_data_parts wdp
                      JOIN wallet_data wd ON wdp.wallet_data_id = wd.id
                      WHERE wd.wallet_id = ? AND wdp.user_id = ?";
        if ($stmt_parts = $link->prepare($sql_parts)) {
            $stmt_parts->bind_param("ii", $wallet_id, $user_id);
            $stmt_parts->execute();
            $stmt_parts->close();
        } else {
            throw new Exception("Error preparing query for deleting wallet data parts.");
        }

        // 2. Delete walletData entries associated with this wallet.
        $sql_walletData = "DELETE FROM wallet_data WHERE wallet_id = ? AND user_id = ?";
        if ($stmt_walletData = $link->prepare($sql_walletData)) {
            $stmt_walletData->bind_param("ii", $wallet_id, $user_id);
            $stmt_walletData->execute();
            $stmt_walletData->close();
        } else {
            throw new Exception("Error preparing query for deleting wallet data.");
        }

        // 3. Delete the wallet itself.
        $sql_wallet = "DELETE FROM wallets WHERE id = ? AND user_id = ?";
        if ($stmt_wallet = $link->prepare($sql_wallet)) {
            $stmt_wallet->bind_param("ii", $wallet_id, $user_id);
            $stmt_wallet->execute();
            $stmt_wallet->close();
        } else {
            throw new Exception("Error preparing query for deleting wallet.");
        }

        // Commit transaction if all deletions succeed.
        $link->commit();

        $success_message = "Portafoglio eliminato con successo.";
        header("refresh:3; url=dashboard.php");
    } catch (Exception $e) {
        // Rollback the transaction if any deletion fails.
        $link->rollback();
        $error_message = $e->getMessage();
    }

    $link->close();
} else {
    $error_message = "Invalid request. No wallet ID provided.";
}
?>
