<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("location: ../login.php");
    exit;
}

require_once '../config.php';

$user_id = $_SESSION['id'];

// Usa i parametri di mese e anno selezionati se forniti tramite GET o POST
if (isset($_POST['month']) && isset($_POST['year'])) {
    $selectedMonth = $_POST['month'];
    $selectedYear = $_POST['year'];
} elseif (isset($_GET['month']) && isset($_GET['year'])) {
    $selectedMonth = $_GET['month'];
    $selectedYear = $_GET['year'];
} else {
    // Altrimenti, usa mese e anno correnti
    $selectedMonth = date('m');
    $selectedYear = date('Y');
}

// Creo una funzione per formattare le date nel formato gg/mm/aaaa
function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

$today = new DateTime();
$selectedYearMonth = $selectedYear . '-' . $selectedMonth;

// Ottieni il numero di giorni nel mese selezionato
$daysInSelectedMonth = cal_days_in_month(CAL_GREGORIAN, $selectedMonth, $selectedYear);

// Se oggi è un giorno maggiore del numero di giorni nel mese selezionato, imposta il giorno all'ultimo giorno del mese selezionato
if ($today->format('d') > $daysInSelectedMonth) {
    $today->setDate($selectedYear, $selectedMonth, $daysInSelectedMonth);
}

$selectedDate = DateTime::createFromFormat('Y-m', $selectedYearMonth);
$nextMonthDate = (clone $selectedDate)->modify('+1 month');
$leftIncomes = 0;
$recurringSavings = 0;
$estimatedSavings = 0;

// Creo una funzione per eseguire le query SQL
function executeQuery($link, $sql, $params, $singleRow = true) {
    $data = [];
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param(...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($singleRow) {
            $data = $result->fetch_assoc();
        } else {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        $stmt->close();
    }
    return $data;
}

// Recupero la somma di tutte le entrate inserite dall'utente corrente
$totalIncomes = executeQuery(
	$link,
	"SELECT SUM(amount) as total_wallet_incomes
	 FROM wallet_incomes
	 WHERE user_id = ?",
	["i", $user_id]
)['total_wallet_incomes'];

// Recupero le singole entrate dell'utente corrente
$wallet_incomes = executeQuery(
	$link,
	"SELECT id, name, amount, added_date
	 FROM wallet_incomes
	 WHERE user_id = ?
	 ORDER BY added_date DESC",
	["i", $user_id],
	false
);

// Recupero le entrate dell'utente corrente e del mese selezionato
$thisMonthIncomes = executeQuery(
	$link,
	"SELECT SUM(amount) as thisMonthIncomes
	 FROM wallet_incomes
	 WHERE user_id = ?
	 AND DATE_FORMAT(added_date, '%Y-%m') = ?",
	["is", $user_id, $selectedYearMonth]
)['thisMonthIncomes'];

// Recupero la somma di tutte le voci a salvadanaio inserite dall'utente corrente
$totalPiggyBank = executeQuery(
	$link,
	"SELECT SUM(amount) as total_wallet_piggy_bank
	 FROM wallet_piggy_bank
	 WHERE user_id = ?",
	["i", $user_id]
)['total_wallet_piggy_bank'];

// Recupero i portafogli dell'utente corrente
$wallets  = executeQuery(
	$link,
	"SELECT id, user_id, description, icon, show_in_dashboard, shared_with, deleted_at
	 FROM wallets
	 WHERE user_id = ?
	 ORDER BY id ASC",
	["i", $user_id],
	false
);

// Conto i portafogli abilitati in dashboard
$maxDashboardWallets = executeQuery(
    $link,
    "SELECT COUNT(*) AS count
	FROM wallets
	WHERE user_id = ?
	AND show_in_dashboard = 1",
    ["i", $user_id]
);
$disableDashboard = ($maxDashboardWallets['count'] >= 5) ? 'disabled' : '';
$dashboardLabelClass = ($maxDashboardWallets['count'] >= 5) ? 'text-muted' : '';

// Recupero i portafogli condivisi con l'utente corrente
$sharedWallets = executeQuery(
    $link,
    "SELECT w.id, w.description, w.icon, w.show_in_dashboard, w.shared_with, w.deleted_at, w.user_id, u.username AS owner_username
     FROM wallets w 
     JOIN users u ON w.user_id = u.id 
     WHERE JSON_CONTAINS(w.shared_with, ? , '$') AND w.user_id != ? AND w.deleted_at IS NULL 
     ORDER BY w.id ASC",
    ["si", json_encode(["id" => $user_id]), $user_id],
    false
);

// Recupero tutte le spese di ciascun portafogli dell'utente corrente
$ownedWalletIds = [];
if (!empty($wallets)) {
    foreach ($wallets as $wallet) {
        $ownedWalletIds[] = $wallet['id'];
    }
}

if (!empty($ownedWalletIds)) {
    $placeholders = implode(',', array_fill(0, count($ownedWalletIds), '?'));
    $paramTypes = str_repeat('i', count($ownedWalletIds));
    // Notice we select user_id as created_by to know who added each expense.
    $walletDatas = executeQuery(
        $link,
        "SELECT wd.id, wd.wallet_id, wd.description, wd.amount, wd.buying_date, wd.user_id AS created_by, u.username
         FROM wallet_data wd
		 JOIN users u ON wd.user_id = u.id
         WHERE wallet_id IN ($placeholders) 
         ORDER BY buying_date DESC",
        array_merge([$paramTypes], $ownedWalletIds),
        false
    );
} else {
    $walletDatas = [];
}

// Recupero tutte le spese di ciascun portafogli condiviso con l'utente corrente
$sharedWalletIds = array();
if (isset($sharedWallets) && !empty($sharedWallets)) {
    foreach ($sharedWallets as $wallet) {
        $sharedWalletIds[] = $wallet['id'];
    }
}
if (!empty($sharedWalletIds)) {
    // Prepare an IN clause with placeholders (e.g. "?, ?, ?")
    $placeholders = implode(',', array_fill(0, count($sharedWalletIds), '?'));
    // All wallet IDs are integers
    $paramTypes = str_repeat('i', count($sharedWalletIds));
    $query = "SELECT wd.id, wd.wallet_id, wd.description, wd.amount, wd.buying_date, wd.user_id AS created_by, u.username
              FROM wallet_data wd
			  JOIN users u ON wd.user_id = u.id
              WHERE wallet_id IN ($placeholders) 
              ORDER BY buying_date DESC";
    // Execute the query with the wallet IDs as parameters
    $sharedWalletDatas = executeQuery($link, $query, array_merge([$paramTypes], $sharedWalletIds), false);
} else {
    $sharedWalletDatas = array();
}

// Costruisco un array di tutte le spese di ciascun portafogli
$allWalletDataIds = [];
if (!empty($walletDatas)) {
    foreach ($walletDatas as $wd) {
        $allWalletDataIds[] = $wd['id'];
    }
}
if (!empty($sharedWalletDatas)) {
    foreach ($sharedWalletDatas as $wd) {
        $allWalletDataIds[] = $wd['id'];
    }
}

if (!empty($allWalletDataIds)) {
    $placeholders = implode(',', array_fill(0, count($allWalletDataIds), '?'));
    $paramTypes = str_repeat('i', count($allWalletDataIds));
    $query = "SELECT id, wallet_data_id, part_name, part_cost 
              FROM wallet_data_parts 
              WHERE wallet_data_id IN ($placeholders)";
    $allWalletDataParts = executeQuery($link, $query, array_merge([$paramTypes], $allWalletDataIds), false);
} else {
    $allWalletDataParts = array();
}

// Costruisco un array di tutti i portafogli condivisi
$allSharedWallets = [];
// Portafogli principali
if (!empty($wallets)) {
    foreach ($wallets as $wallet) {
        $shared = json_decode($wallet['shared_with'], true);
        if (is_array($shared) && count($shared) > 0) {
            $allSharedWallets[] = $wallet;
        }
    }
}
//Portafogli condivisi con l'utente:
if (!empty($sharedWallets)) {
    foreach ($sharedWallets as $wallet) {
        $allSharedWallets[] = $wallet;
    }
}

$sharedWalletSettlements = [];

foreach ($allSharedWallets as $wallet) {
	if ($wallet['user_id'] == $user_id) {
		$expenses = array_merge(
			 array_filter($walletDatas, function($d) use ($wallet) {
				 return $d['wallet_id'] == $wallet['id'];
			 }),
			 array_filter($sharedWalletDatas, function($d) use ($wallet) {
				 return $d['wallet_id'] == $wallet['id'];
			 })
		);
	} else {
		$expenses = array_filter($sharedWalletDatas, function($d) use ($wallet) {
			return $d['wallet_id'] == $wallet['id'];
		});
	}
   
    $totalExpense = 0;
    $participants = [];
    
    $ownerId = $wallet['user_id'];
    $ownerUsername = ($ownerId == $user_id) ? $_SESSION['username'] : $wallet['owner_username'];
    $participants[$ownerId] = ['username' => $ownerUsername, 'total' => 0];
    
    $sharedParticipants = json_decode($wallet['shared_with'], true);
    if (is_array($sharedParticipants)) {
        foreach ($sharedParticipants as $p) {
            $participants[$p['id']] = ['username' => $p['username'], 'total' => 0];
        }
    }
    
    foreach ($expenses as $exp) {
        $totalExpense += $exp['amount'];
        $creator = $exp['created_by'];
        if (!isset($participants[$creator])) {
            $participants[$creator] = ['username' => 'Unknown', 'total' => 0];
        }
        $participants[$creator]['total'] += $exp['amount'];
    }
    
    $numParticipants = count($participants);
    $equalShare = ($numParticipants > 0) ? $totalExpense / $numParticipants : 0;
    
    foreach ($participants as $id => $info) {
        $participants[$id]['net'] = round($info['total'] - $equalShare, 2);
    }
    
    $sharedWalletSettlements[$wallet['id']] = [
        'totalExpense' => round($totalExpense, 2),
        'equalShare'   => round($equalShare, 2),
        'participants' => $participants
    ];
}

// Creo un array associativo delle spese per ID
$walletDataPartsById = array();
foreach ($allWalletDataParts as $part) {
    $walletDataPartsById[$part['wallet_data_id']][] = $part;
}

// Recupero i portafogli da mostrare in dashboard
$dashboardWallets = [];
if (!empty($wallets)) {
    foreach ($wallets as $wallet) {
        if ($wallet['show_in_dashboard']) {
            $dashboardWallets[] = $wallet;
        }
    }
}

// Recupero le singole voci a salvadanaio dell'utente corrente
$piggyBankEntries = executeQuery(
	$link,
	"SELECT id, name, amount, added_date
	 FROM wallet_piggy_bank
	 WHERE user_id = ?
	 ORDER BY added_date DESC",
	["i", $user_id],
	false
);

// Recupero le voci a salvadanaio dell'utente corrente e del mese selezionato
$thisMonthPiggyBank = executeQuery(
	$link,
	"SELECT SUM(amount) as total_wallet_piggy_bank_current_month
	 FROM wallet_piggy_bank
	 WHERE user_id = ?
	 AND DATE_FORMAT(added_date, '%Y-%m') = ?",
	["is", $user_id, $selectedYearMonth]
)['total_wallet_piggy_bank_current_month'];

// Recupero la data dello stipendio definita dall'utente
$salaryDate = executeQuery($link, "SELECT salary_date FROM users WHERE id = ?", ["i", $user_id])['salary_date'];

// Inizializzo le variabili relative alle spese totali
$totalExpenses = 0;
$thisMonthTotalRecurringExpenses = 0;
$thisMonthTotalEstimatedExpenses = 0;

// Creo una funzione per processare le spese ricorrenti e stimate
function processExpenses($link, $user_id, $selectedDate, $salaryDate, &$expenses, &$totalExpenses, &$savings, $tableName) {
    $sql = "SELECT id, name, amount, start_month, start_year, end_month, end_year, undetermined, debit_date, billing_frequency
            FROM $tableName
            WHERE user_id = ?";

    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($id, $name, $amount, $startMonth, $startYear, $endMonth, $endYear, $undetermined, $debitDate, $billingFrequency);
        while ($stmt->fetch()) {
            $amount = (float) $amount;
            $billingFrequency = (int) $billingFrequency;

            $startDate_string = $startYear . '-' . $startMonth;
            $endDate_string = $endYear ? $endYear . '-' . $endMonth : null;
            $startDate = DateTime::createFromFormat('Y-m', $startDate_string);
            if (!$startDate) continue;

            $relevantDate = $selectedDate;

            $interval = $startDate->diff($relevantDate);
            if ($startDate > $relevantDate) {
                $currentInstallment = 0;
            } else {
                if ($billingFrequency == 1) {
                    if ($endDate_string) {
                        $currentInstallment = ($interval->y * 12 + $interval->m) + 1;
                    } else {
                        $currentInstallment = 1;
                    }
                } else {
					if($debitDate > $salaryDate){
						$monthsPassed = ($interval->y * 12 + $interval->m -1) + 1;
						$totalPeriods = intdiv($monthsPassed, $billingFrequency);
						$currentPeriod_months = $monthsPassed % $billingFrequency;
						$currentInstallment = $currentPeriod_months + 1;
					} else {
						$monthsPassed = ($interval->y * 12 + $interval->m) + 1;
						$totalPeriods = intdiv($monthsPassed, $billingFrequency);
						$currentPeriod_months = $monthsPassed % $billingFrequency;
						$currentInstallment = $currentPeriod_months + 1;
					}
                }
            }

            $totalInstallments = 1;
            if ($endDate_string) {
                $endDate = DateTime::createFromFormat('Y-m', $endDate_string);
                if ($endDate) {
                    $totalInterval = $startDate->diff($endDate);
                    if ($billingFrequency == 1) {
                        $totalInstallments = ($totalInterval->y * 12 + $totalInterval->m) + 1;
                    } else {
                        $totalInstallments = $billingFrequency;
                    }

                    if ($endDate < $relevantDate) {
                        $currentInstallment = 0;
                    }
                }
            } else {
                if ($billingFrequency > 1) {
                    $totalInstallments = $billingFrequency;
                }
            }

			$monthlyAmount = 0;
			if ($currentInstallment != 0){
			$monthlyAmount = $amount / $billingFrequency;
			}

            if ($endDate_string && $billingFrequency > 1){
                $nextDebitDate = (clone $startDate)->modify('+' . $billingFrequency . ' months');
            } else {
                $nextDebitDate = (clone $startDate)->modify('+' . $billingFrequency . ' months - 1 month');
            }

            while ($nextDebitDate < $relevantDate) {
                $nextDebitDate->modify('+' . $billingFrequency . ' months');
            }
            if ($endDate_string) {
                $endDate = DateTime::createFromFormat('Y-m', $endDate_string);
                if ($nextDebitDate > $endDate) {
                    $nextDebitDate = $endDate;
                }
            }
            $nextDebitDate->setDate($nextDebitDate->format('Y'), $nextDebitDate->format('m'), $debitDate);
            $nextDebitDate_formatted = $nextDebitDate->format('d/m/Y');

            if ($billingFrequency > 1 && ($endDate > $selectedDate || $undetermined == 1)) {
                $savings += $monthlyAmount * $currentInstallment;
            } else if ($billingFrequency == 1 && ($endDate > $selectedDate || $undetermined == 1)) {
                $savings += $monthlyAmount;
            } else {
				$savings += $monthlyAmount;
			}

			$totalExpenses += $monthlyAmount;

            $expenses[] = [
                'id' => $id,
                'name' => $name,
                'amount' => $amount,
                'start_month' => $startMonth,
                'start_year' => $startYear,
                'end_month' => $endMonth,
                'end_year' => $endYear,
                'undetermined' => $undetermined,
                'debit_date' => $debitDate,
                'billing_frequency' => $billingFrequency,
                'monthly_debit' => round($monthlyAmount, 2),
                'current_installment' => $currentInstallment,
                'total_installments' => $totalInstallments,
                'monthly_amount' => $monthlyAmount,
                'next_debit_date' => $nextDebitDate_formatted
            ];
        }

        $totalExpenses = round($totalExpenses, 2);
        $savings = round($savings, 2);
        $stmt->close();
    }
}

// Recupero tutte le spese ricorrenti
$recurringExpenses = [];
$thisMonthTotalRecurringExpenses = 0;
$recurringSavings = 0;
processExpenses($link, $user_id, $selectedDate, $salaryDate, $recurringExpenses, $thisMonthTotalRecurringExpenses, $recurringSavings, 'wallet_recurring_expenses');

// Recupero tutte le spese stimate
$estimatedExpenses = [];
$thisMonthTotalEstimatedExpenses = 0;
$estimatedSavings = 0;
processExpenses($link, $user_id, $selectedDate, $salaryDate, $estimatedExpenses, $thisMonthTotalEstimatedExpenses, $estimatedSavings, 'wallet_estimated_expenses');

// Recupero le spese extra
$extraExpenses = executeQuery(
	$link,
	"SELECT id, name, amount, debit_date
	 FROM wallet_extra_expenses
	 WHERE user_id = ?
	 ORDER BY debit_date DESC",
	["i", $user_id],
	false
);

// Recupero le spese extra relative al mese corrente
$thismonthExtraExpenses = [];

if ($salaryDate) {
    // Salary date non nullo
    $startDate = (new DateTime())->setDate($selectedYear, $selectedMonth, $salaryDate);
    $endDate = (clone $startDate)->modify('+1 month')->modify('-1 day');
    $thismonthExtraExpenses = executeQuery(
		$link,
		"SELECT id, name, amount, debit_date
		 FROM wallet_extra_expenses
		 WHERE user_id = ?
		 AND debit_date BETWEEN ? AND ?",
		["iss", $user_id, $startDate->format('Y-m-d'),
		$endDate->format('Y-m-d')],
		false
	);
} else {
    // Salary date nullo, considero il mese normalmente
    $thismonthExtraExpenses = executeQuery(
		$link,
		"SELECT id, name, amount, debit_date
		 FROM wallet_extra_expenses
		 WHERE user_id = ?
		 AND MONTH(debit_date) = ?
		 AND YEAR(debit_date) = ?",
		["iis", $user_id, $selectedMonth, $selectedYear],
		false
	);
}

// Recupero le spese relative al mese corrente per i portafogli da mostrare in dashboard
$walletDashboardExpenses = [];
$thisMonthTotalCustomWallets = 0;
foreach ($dashboardWallets as $wallet) {
    $walletExpenseRow = executeQuery(
        $link,
        "SELECT SUM(amount) as total 
         FROM wallet_data
         WHERE wallet_id = ? 
         AND DATE_FORMAT(buying_date, '%Y-%m') = ?",
        ["is", $wallet['id'], $selectedYear . '-' . $selectedMonth]
    );
    // Only add if there is a non-null total (default to 0 otherwise)
    $walletDashboardExpenses[$wallet['description']] = $walletExpenseRow['total'] ?: 0;
	$thisMonthTotalCustomWallets = $thisMonthTotalCustomWallets + $walletExpenseRow['total'];
}

// Separate keys and values for later use in JavaScript
$walletLabels = array_keys($walletDashboardExpenses);
$walletValues = array_values($walletDashboardExpenses);

// Calcolo il totale delle spese extra del mese in corso
$thisMonthTotalExtraExpenses = array_sum(array_column($thismonthExtraExpenses, 'amount'));

// Calcolo le spese totali come somma di quelle ricorrenti, stimate, extra e la somma messa nel salvadanaio per il mese corrente
$totalExpenses = round($thisMonthTotalRecurringExpenses + $thisMonthTotalEstimatedExpenses + $thisMonthTotalExtraExpenses + $thisMonthTotalCustomWallets, 2);

// Calcolo lo stipendio rimanente
$leftIncomes = round($thisMonthIncomes - $totalExpenses - $thisMonthPiggyBank, 2);

// Recupero gli ultimi 12 mesi
$last_12_months = [];
$last_12_monthlyIncomes = [];
$last_12_monthlyExpenses = [];
$monthlyPiggyBank = []; // Aggiungo questa riga per tenere traccia delle somme nel salvadanaio

for ($i = 0; $i < 12; $i++) {
    $date = new DateTime();
    $date->modify("-$i months");
    $month_year = $date->format('Y-m');
    $last_12_months[] = $date->format('F Y');

    // Recupero entrate per il mese corrente
    $monthlyIncome = executeQuery(
		$link,
		"SELECT SUM(amount) as monthly_wallet_incomes
		 FROM wallet_incomes
		 WHERE user_id = ?
		 AND DATE_FORMAT(added_date, '%Y-%m') = ?",
		["is", $user_id, $month_year]
	)['monthly_wallet_incomes'];
    $last_12_monthlyIncomes[] = round($monthlyIncome,2) ?: 0;

    // Recupero uscite per il mese corrente
    $monthlyExpense = 0;

    // Spese ricorrenti
    $rows = executeQuery(
		$link,
		"SELECT amount, billing_frequency
		 FROM wallet_recurring_expenses
		 WHERE user_id = ?
		 AND (start_year < ? OR (start_year = ? AND start_month <= ?))
		 AND (end_year IS NULL OR end_year > ? OR (end_year = ? AND end_month >= ?))",
		["iiiiiii", $user_id, $date->format('Y'), $date->format('Y'), $date->format('m'), $date->format('Y'), $date->format('Y'), $date->format('m')],
		false
	);
    foreach ($rows as $row) {
        $monthlyExpense += $row['amount'] / $row['billing_frequency'];
    }

    // Spese stimate
    $rows = executeQuery(
		$link,
		"SELECT amount, billing_frequency
		 FROM wallet_estimated_expenses
		 WHERE user_id = ?
		 AND (start_year < ? OR (start_year = ? AND start_month <= ?))
		 AND (end_year IS NULL OR end_year > ? OR (end_year = ? AND end_month >= ?))",
		["iiiiiii", $user_id, $date->format('Y'), $date->format('Y'), $date->format('m'), $date->format('Y'), $date->format('Y'), $date->format('m')],
		false
	);
    foreach ($rows as $row) {
        $monthlyExpense += $row['amount'] / $row['billing_frequency'];
    }

    // Spese extra
    if ($salaryDate) {
        // Salary date non nullo
        $startDate = (clone $date)->setDate($date->format('Y'), $date->format('m'), $salaryDate);
        $endDate = (clone $startDate)->modify('+1 month')->modify('-1 day');
        $extraRow = executeQuery(
			$link,
			"SELECT SUM(amount) as total_extra
			 FROM wallet_extra_expenses
			 WHERE user_id = ?
			 AND debit_date BETWEEN ? AND ?",
			["iss", $user_id, $startDate->format('Y-m-d'), $endDate->format('Y-m-d')]
		);
        $monthlyExpense += $extraRow['total_extra'] ?: 0;
    } else {
        // Salary date nullo, considero il mese normalmente
        $extraRow = executeQuery(
			$link,
			"SELECT SUM(amount) as total_extra
			 FROM wallet_extra_expenses
			 WHERE user_id = ?
			 AND MONTH(debit_date) = ?
			 AND YEAR(debit_date) = ?",
			["iis", $user_id, $date->format('m'), $date->format('Y')]
		);
        $monthlyExpense += $extraRow['total_extra'] ?: 0;
    }

	// Recupero le spese per i portafogli abilitati alla dashboard
	$walletMonthlyExpense = 0;
	foreach ($dashboardWallets as $wallet) {
		$walletData = executeQuery(
			$link,
			"SELECT SUM(amount) as monthly_total 
			 FROM wallet_data 
			 WHERE wallet_id = ? 
			 AND DATE_FORMAT(buying_date, '%Y-%m') = ?",
			["is", $wallet['id'], $month_year]
		);
		$walletMonthlyExpense += $walletData['monthly_total'] ?: 0;
	}
	// Add this wallet expense to the overall monthly expense
	$monthlyExpense += $walletMonthlyExpense;

    $last_12_monthlyExpenses[] = round($monthlyExpense,2);

    // Recupero l'importo salvadanaio per il mese corrente
    $monthly_piggy = executeQuery(
		$link,
		"SELECT SUM(amount) as monthly_wallet_piggy_bank
		 FROM wallet_piggy_bank
		 WHERE user_id = ?
		 AND DATE_FORMAT(added_date, '%Y-%m') = ?",
		["is", $user_id, $month_year]
	)['monthly_wallet_piggy_bank'];
    $monthlyPiggyBank[] = $monthly_piggy ?: 0;
}

// Inverto gli array per avere i mesi in ordine cronologico
$last_12_months = array_reverse($last_12_months);
$last_12_monthlyIncomes = array_reverse($last_12_monthlyIncomes);
$last_12_monthlyExpenses = array_reverse($last_12_monthlyExpenses);
$monthlyPiggyBank = array_reverse($monthlyPiggyBank);

// Calcoli in Dashboard
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['month']) && isset($_POST['year'])) {
    $selectedMonth = $_POST['month'];
    $selectedYear = $_POST['year'];
} else {
    $selectedMonth = date('m');
    $selectedYear = date('Y');
}

if ($salaryDate) {
    $currentDate = DateTime::createFromFormat('Y-m-d', "$selectedYear-$selectedMonth-$salaryDate");
    $next_date = clone $currentDate;
    $next_date->modify('+1 month')->modify('-1 day');
} else {
    $currentDate = DateTime::createFromFormat('Y-m-d', "$selectedYear-$selectedMonth-01");
    $next_date = clone $currentDate;
    $next_date->modify('+1 month')->modify('-1 day');
}

$currentSelected = $currentDate->format('d/m/Y');
$next_selected = $next_date->format('d/m/Y');

?>
