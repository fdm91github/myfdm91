<?php
// php -d detect_unicode=0 /var/www/myfdm91/cron/cron_due_payment_reminders.php
declare(strict_types=1);

date_default_timezone_set('Europe/Rome');

require_once __DIR__ . '/config.php';       // deve valorizzare $link (mysqli) e $config
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// === CONFIG ===
$USE_PHPMAILER = true;               // invio autenticato con STARTTLS/SMTPS da config.php
$OFFSETS       = [7, 3, 1];          // giorni pre-scadenza

// === FUNZIONI UTILI ===
function q(\mysqli $link, string $sql, array $bind = [], bool $singleRow = false) {
    $stmt = $link->prepare($sql);
    if (!$stmt) { throw new RuntimeException($link->error); }
    if ($bind) { $stmt->bind_param(...$bind); }
    $stmt->execute();
    $res = $stmt->get_result();
    if ($singleRow) {
        $row = $res->fetch_assoc() ?: [];
        $stmt->close();
        return $row;
    }
    $out = [];
    while ($r = $res->fetch_assoc()) { $out[] = $r; }
    $stmt->close();
    return $out;
}

/** Calcola la prossima data di addebito (>= $today) */
function compute_next_due_date(array $e, DateTime $today): ?DateTime {
    $startYear  = (int)$e['start_year'];
    $startMonth = (int)$e['start_month'];
    $endYear    = $e['end_year'] ? (int)$e['end_year'] : null;
    $endMonth   = $e['end_month'] ? (int)$e['end_month'] : null;
    $undetermined = (int)$e['undetermined'] === 1;
    $debitDay   = max(1, min(28, (int)$e['debit_date'] ?: 1)); // 1..28 per evitare mesi corti
    $freq       = max(1, (int)$e['billing_frequency']);

    $cursor = DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $startYear, $startMonth, $debitDay));
    if (!$cursor) return null;

    $end = null;
    if (($endYear && $endMonth) && !$undetermined) {
        $end = DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $endYear, $endMonth, $debitDay));
    }

    while ($cursor < $today) {
        $cursor->modify('+' . $freq . ' months');
        if ($end && $cursor > $end) { return null; }
    }
    if ($end && $cursor > $end) { return null; }
    return $cursor;
}

function format_date_it(DateTime $d): string { return $d->format('d/m/Y'); }

/** Invio email (PHPMailer o mail()) */
function send_email(string $to, string $subject, string $html, string $text, array $smtpCfg, array $fromCfg, bool $usePhpMailer): bool {
    if ($usePhpMailer) {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = $smtpCfg['host'] ?? 'localhost';
            $mail->Port       = (int)($smtpCfg['port'] ?? 587);
            $mail->SMTPAuth   = (bool)($smtpCfg['auth'] ?? true);
            $mail->Username   = $smtpCfg['username'] ?? null;
            $mail->Password   = $smtpCfg['password'] ?? null;

            $secure = strtolower((string)($smtpCfg['secure'] ?? 'tls'));
            if ($secure === 'ssl' || $mail->Port === 465) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;      // 465
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;   // 587
            }
            $mail->SMTPAutoTLS = (bool)($smtpCfg['autoTLS'] ?? true);
            if (!empty($smtpCfg['options'])) {
                $mail->SMTPOptions = $smtpCfg['options'];
            }

            $mail->CharSet = 'UTF-8';
            $mail->setFrom($fromCfg['from_address'], $fromCfg['from_name']);
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $html;
            $mail->AltBody = $text;
            $mail->send();
            return true;
        } catch (\Throwable $e) {
            error_log('PHPMailer error: ' . $e->getMessage());
            return false;
        }
    } else {
        $headers = [];
        $headers[] = 'From: ' . sprintf('"%s" <%s>', addslashes($fromCfg['from_name']), $fromCfg['from_address']);
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=UTF-8';
        return @mail($to, $subject, $html, implode("\r\n", $headers));
    }
}

/** Log invio per idempotenza */
function log_sent(\mysqli $link, int $userId, string $table, int $expenseId, DateTime $due, int $offset): void {
    $sql = "INSERT IGNORE INTO wallet_payment_reminders
            (user_id, expense_table, expense_id, due_date, offset_days)
            VALUES (?, ?, ?, ?, ?)";
    $dueStr = $due->format('Y-m-d');
    $stmt = $link->prepare($sql);
    $stmt->bind_param("isisi", $userId, $table, $expenseId, $dueStr, $offset);
    $stmt->execute();
    $stmt->close();
}

/** Controlla se già inviato */
function already_sent(\mysqli $link, int $userId, string $table, int $expenseId, DateTime $due, int $offset): bool {
    $row = q($link,
        "SELECT id FROM wallet_payment_reminders
         WHERE user_id = ? AND expense_table = ? AND expense_id = ? AND due_date = ? AND offset_days = ? LIMIT 1",
         ["isisi", $userId, $table, $expenseId, $due->format('Y-m-d'), $offset],
         true
    );
    return !empty($row);
}

// === MAIN ===
// Solo utenti verificati con email
$users = q(
    $link,
    "SELECT id, username, email
     FROM users
     WHERE verified = 1
       AND email IS NOT NULL
       AND email <> ''"
);

$today = new DateTime('today');  // 00:00 locale

foreach ($users as $u) {
    $userId = (int)$u['id'];
    $email  = $u['email'];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { continue; }

    // Raccogliamo TUTTE le notifiche dovute per l'utente, poi inviamo UNA sola email
    $notifications = []; // array di item: table, id, name, type, amount, freq, due(DateTime), daysLeft, monthly
    $grouped       = [1 => [], 3 => [], 7 => []]; // per layout email

    // Spese ricorrenti
    $rec = q($link, "SELECT id, name, amount, start_month, start_year, end_month, end_year, undetermined, debit_date, billing_frequency
                     FROM wallet_recurring_expenses WHERE user_id = ?", ["i", $userId]);
    foreach ($rec as $e) {
        $e['_table'] = 'wallet_recurring_expenses';
        $due = compute_next_due_date($e, $today);
        if (!$due) { continue; }
        $daysLeft = (int)$today->diff($due)->format('%a');
        if (!in_array($daysLeft, [7,3,1], true)) { continue; }
        if (already_sent($link, $userId, $e['_table'], (int)$e['id'], $due, $daysLeft)) { continue; }

        $amount = (float)$e['amount'];
        $freq   = max(1, (int)$e['billing_frequency']);
        $notifications[] = [
            'table'     => $e['_table'],
            'id'        => (int)$e['id'],
            'name'      => $e['name'],
            'type'      => 'Spesa ricorrente',
            'amount'    => $amount,
            'freq'      => $freq,
            'monthly'   => round($amount / $freq, 2),
            'due'       => $due,
            'daysLeft'  => $daysLeft,
        ];
        $grouped[$daysLeft][] = count($notifications) - 1; // indice verso $notifications
    }

    // Spese stimate
    $est = q($link, "SELECT id, name, amount, start_month, start_year, end_month, end_year, undetermined, debit_date, billing_frequency
                     FROM wallet_estimated_expenses WHERE user_id = ?", ["i", $userId]);
    foreach ($est as $e) {
        $e['_table'] = 'wallet_estimated_expenses';
        $due = compute_next_due_date($e, $today);
        if (!$due) { continue; }
        $daysLeft = (int)$today->diff($due)->format('%a');
        if (!in_array($daysLeft, [7,3,1], true)) { continue; }
        if (already_sent($link, $userId, $e['_table'], (int)$e['id'], $due, $daysLeft)) { continue; }

        $amount = (float)$e['amount'];
        $freq   = max(1, (int)$e['billing_frequency']);
        $notifications[] = [
            'table'     => $e['_table'],
            'id'        => (int)$e['id'],
            'name'      => $e['name'],
            'type'      => 'Spesa stimata',
            'amount'    => $amount,
            'freq'      => $freq,
            'monthly'   => round($amount / $freq, 2),
            'due'       => $due,
            'daysLeft'  => $daysLeft,
        ];
        $grouped[$daysLeft][] = count($notifications) - 1;
    }

    if (empty($notifications)) {
        continue; // niente da inviare a questo utente
    }

    // ====== Costruzione mail cumulativa ======
    // Ordina per urgenza: 1 -> 3 -> 7 giorni
    $order = [1, 3, 7];

    $sectionsHtml = '';
    $sectionsText = '';
    foreach ($order as $d) {
        if (empty($grouped[$d])) continue;

        $title = "Scadenze tra {$d} giorn" . ($d>1?'i':'o');
        $sectionsHtml .= "<h3 style=\"margin:16px 0 8px;\">{$title}</h3>";
        $sectionsHtml .= "<table cellpadding=\"6\" cellspacing=\"0\" border=\"0\" style=\"border-collapse:collapse; width:100%;\">";
        $sectionsHtml .= "<thead><tr>"
                       . "<th align=\"left\">Tipo</th>"
                       . "<th align=\"left\">Descrizione</th>"
                       . "<th align=\"left\">Scadenza</th>"
                       . "<th align=\"right\">Importo</th>"
                       . "<th align=\"right\">Frequenza</th>"
                       . "<th align=\"right\">Quota mensile</th>"
                       . "</tr></thead><tbody>";

        $sectionsText .= strtoupper($title) . "\n";

        foreach ($grouped[$d] as $idx) {
            $it = $notifications[$idx];
            $sectionsHtml .= "<tr>"
                           . "<td>{$it['type']}</td>"
                           . "<td>".htmlspecialchars($it['name'], ENT_QUOTES, 'UTF-8')."</td>"
                           . "<td>".format_date_it($it['due'])."</td>"
                           . "<td align=\"right\">".number_format($it['amount'], 2, ',', '.')." €</td>"
                           . "<td align=\"right\">ogni {$it['freq']} mesi</td>"
                           . "<td align=\"right\">".number_format($it['monthly'], 2, ',', '.')." €</td>"
                           . "</tr>";

            $sectionsText .= "- {$it['type']} | {$it['name']} | Scadenza: ".format_date_it($it['due'])
                           ." | Importo: ".number_format($it['amount'], 2, ',', '.')." €"
                           ." | Freq: {$it['freq']} mesi"
                           ." | Quota: ".number_format($it['monthly'], 2, ',', '.')." €\n";
        }
        $sectionsHtml .= "</tbody></table>";
        $sectionsText .= "\n";
    }

    $count = count($notifications);
    $subject = "Promemoria: {$count} spesa".($count>1?'e':'')." in scadenza";
    $html = "
        <p>Ciao {$u['username']},</p>
        <p>abbiamo trovato <strong>{$count}</strong> spesa".($count>1?'e':'')." in scadenza nei prossimi giorni:</p>
        {$sectionsHtml}
        <p style=\"margin-top:16px;\">Questo è un promemoria automatico di MyFDM91.</p>
    ";
    $text = "Ciao {$u['username']},\nAbbiamo trovato {$count} spesa".($count>1?'e':'')." in scadenza nei prossimi giorni:\n\n"
          . $sectionsText
          . "— Promemoria automatico MyFDM91\n";

    // Invio UNA sola email cumulativa
    $sent = send_email(
        $email,
        $subject,
        $html,
        $text,
        $GLOBALS['config']['smtp'],
        $GLOBALS['config']['email'],
        $GLOBALS['USE_PHPMAILER']
    );

    // Se invio OK, logga ognuno dei promemoria inclusi (idempotenza)
    if ($sent) {
        foreach ($notifications as $it) {
            log_sent($link, $userId, $it['table'], (int)$it['id'], $it['due'], (int)$it['daysLeft']);
        }
    } else {
        error_log("Mail cumulativa fallita a {$email} (user {$userId}).");
    }
}

echo "[".date('Y-m-d H:i:s')."] Done\n";

