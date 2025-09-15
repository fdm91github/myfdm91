<?php
declare(strict_types=1);

require_once '../config.php';

// Forzo mysqli a generare eccezioni: così posso usare try/catch in modo pulito
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // (1) Valido l'input
    $token = $_GET['token'] ?? '';
    if ($token === '') {
        // Reindirizzo: non espongo dettagli tecnici
        header("Location: https://my.fdm91.net/mysettings?email_verified=0&reason=missing_token", true, 302);
        exit;
    }

    // (Opzionale ma utile) – verifico il formato del token: qui assumo base64url/hex 32+ char
    if (!preg_match('/^[A-Za-z0-9_-]{32,}$/', $token)) {
        header("Location: https://my.fdm91.net/mysettings?email_verified=0&reason=bad_token_format", true, 302);
        exit;
    }

    // (2) Mi assicuro della codifica
    $link->set_charset('utf8mb4');

    // (3) Verifico il token (non scaduto)
    $sql  = "SELECT user_id, email
             FROM email_verifications
             WHERE token = ? AND expires >= ? 
             LIMIT 1";
    $stmt = $link->prepare($sql);
    $now  = time();
    $stmt->bind_param("si", $token, $now);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows !== 1) {
        // Token inesistente o scaduto: reindirizzo
        $stmt->close();
        header("Location: https://my.fdm91.net/mysettings?email_verified=0&reason=invalid_or_expired", true, 302);
        exit;
    }

    $stmt->bind_result($user_id, $email);
    $stmt->fetch();
    $stmt->close();

    // (4) Apro una transazione: voglio atomicità tra UPDATE utente e DELETE token
    $link->begin_transaction();

    // (5) Aggiorno l'utente – imposto sia verified che enabled
    $sql  = "UPDATE users SET verified = 1, enabled = 1 WHERE id = ?";
    $stmt = $link->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    // (6) Elimino il token (one-shot) per prevenire riuso
    $sql  = "DELETE FROM email_verifications WHERE token = ?";
    $stmt = $link->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->close();

    // (7) Commit finale: rendo persistenti le modifiche
    $link->commit();

    // (8) Reindirizzo a esito positivo (niente echo prima dell’header)
    header("Location: https://my.fdm91.net/mysettings?email_verified=1", true, 302);
    exit;

} catch (Throwable $e) {
    // (9) In caso di errore, effettuo il rollback in sicurezza
    try { $link->rollback(); } catch (Throwable $ignore) {}

    // (10) Log locale (server) – non espongo dettagli all'utente
    // error_log('Email verification error: ' . $e->getMessage());

    header("Location: https://my.fdm91.net/mysettings?email_verified=0&reason=server_error", true, 302);
    exit;
} finally {
    // (11) Chiudo risorse in ogni caso
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        try { $stmt->close(); } catch (Throwable $ignore) {}
    }
    if (isset($link) && $link instanceof mysqli) {
        try { $link->close(); } catch (Throwable $ignore) {}
    }
}

