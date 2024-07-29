<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("location: ../login.php");
    exit;
}

require_once 'config.php';

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

$today = new DateTime();
$selectedYearMonth = $selectedYear . '-' . $selectedMonth;
$selectedDate = DateTime::createFromFormat('Y-m', $selectedYearMonth);
$nextMonthDate = (clone $selectedDate)->modify('+1 month');
$leftIncomes = 0;
$recurringSavings = 0;
$estimatedSavings = 0;

// Recupero la somma di tutte le entrate inserite dall'utente corrente
$totalIncomes = 0;
$sql = "SELECT SUM(amount) as total_incomes FROM incomes WHERE user_id = ?";
if ($stmt = $link->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($totalIncomes);
    $stmt->fetch();
    $stmt->close();
}

// Recupero le singole entrate dell'utente corrente
$incomes = [];
$sql = "SELECT id, name, amount, added_date FROM incomes WHERE user_id = ? ORDER BY added_date DESC";
if ($stmt = $link->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($id, $name, $amount, $added_date);
    while ($stmt->fetch()) {
        $amount = (float) $amount;
        $incomes[] = [
            'id' => $id,
			'name' => $name,
            'amount' => $amount,
            'added_date' => $added_date
        ];
    }
    $stmt->close();
}

// Recupero le entrate dell'utente corrente e del mese selezionato
$thisMonthIncomes = 0;
$sql = "SELECT SUM(amount) as thisMonthIncomes FROM incomes WHERE user_id = ? AND DATE_FORMAT(added_date, '%Y-%m') = ?";
if ($stmt = $link->prepare($sql)) {
    $stmt->bind_param("is", $user_id, $selectedYearMonth);
    $stmt->execute();
    $stmt->bind_result($thisMonthIncomes);
    $stmt->fetch();
    $stmt->close();
}

// Recupero la somma di tutte le voci a salvadanaio inserite dall'utente corrente
$totalPiggyBank = 0;
$sql = "SELECT SUM(amount) as total_piggy_bank FROM piggy_bank WHERE user_id = ?";
if ($stmt = $link->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($totalPiggyBank);
    $stmt->fetch();
    $stmt->close();
}

// Recupero le singole voci a salvadanaio dell'utente corrente
$piggyBankEntries = [];
$sql = "SELECT id, name, amount, added_date FROM piggy_bank WHERE user_id = ? ORDER BY added_date DESC";
if ($stmt = $link->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($id, $name, $amount, $added_date);
    while ($stmt->fetch()) {
        $amount = (float) $amount;
        $piggyBankEntries[] = [
            'id' => $id,
			'name' => $name,
            'amount' => $amount,
            'added_date' => $added_date
        ];
    }
    $stmt->close();
}

// Recupero le voci a salvadanaio dell'utente corrente e del mese selezionato
$thisMonthPiggyBank = 0;
$sql = "SELECT SUM(amount) as total_piggy_bank_current_month FROM piggy_bank WHERE user_id = ? AND DATE_FORMAT(added_date, '%Y-%m') = ?";
if ($stmt = $link->prepare($sql)) {
    $stmt->bind_param("is", $user_id, $selectedYearMonth);
    $stmt->execute();
    $stmt->bind_result($thisMonthPiggyBank);
    $stmt->fetch();
    $stmt->close();
}

// Recupero la data dello stipendio definita dall'utente
$salaryDate = 0;
$sql = "SELECT salary_date FROM users WHERE id = ?";
if ($stmt = $link->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($salaryDate);
    $stmt->fetch();
    $stmt->close();
}

// Inizializzo le variabili relative alle spese totali
$totalExpenses = 0;
$thisMonthTotalRecurringExpenses = 0;
$thisMonthTotalEstimatedExpenses = 0;

// Recupero tutte le spese ricorrenti
$recurringExpenses = [];
$sql = "SELECT id, name, amount, start_month, start_year, end_month, end_year, undetermined, debit_date, billing_frequency
        FROM recurring_expenses
        WHERE user_id = ?";

if ($stmt = $link->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($id, $name, $amount, $startMonth, $startYear, $endMonth, $endYear, $undetermined, $debitDate, $billingFrequency);
    while ($stmt->fetch()) {
        $amount = (float) $amount;
        $billingFrequency = (int) $billingFrequency;
        $monthlyAmount = $amount / $billingFrequency;  // Dividi l'importo per la frequenza di fatturazione

        $startDate_string = $startYear . '-' . $startMonth;
        $endDate_string = $endYear ? $endYear . '-' . $endMonth : null;
        $startDate = DateTime::createFromFormat('Y-m', $startDate_string);
        if (!$startDate) continue;

        // Determino se considerare la spesa per il mese corrente o il mese successivo
        if ($debitDate >= $salaryDate) {
            $relevantDate = $selectedDate;
        } else {
            $relevantDate = (clone $selectedDate)->modify('+1 month');
        }

        // Calcolo la rata corrente
        $interval = $startDate->diff($relevantDate);
        // Verifico se la data di inizio è nel futuro rispetto alla data rilevante
        if ($startDate > $relevantDate) {
            $currentInstallment = 0;
        } else {
            if ($billingFrequency == 1) {
                if ($endDate_string) {
                    // Se la frequenza di fatturazione è 1 e ci sono date di inizio e fine definite
                    $currentInstallment = ($interval->y * 12 + $interval->m) + 1;
                } else {
                    // Se la frequenza di fatturazione è 1 e non c'è una data di fine definita
                    $currentInstallment = 1;
                }
            } else {
                if ($endDate_string || $undetermined == 1) {
                    // Se la frequenza di fatturazione è maggiore di 1 e ci sono una data di fine o undetermined è 1
                    $monthsPassed = ($interval->y * 12 + $interval->m -1) + 1;
                    $totalPeriods = intdiv($monthsPassed, $billingFrequency);
                    $currentPeriod_months = $monthsPassed % $billingFrequency;
                    $currentInstallment = $currentPeriod_months + 1;  // Periodo corrente, esempio: 1, 2, 3, ... , n
                } else {
                    // Se la frequenza di fatturazione è maggiore di 1 e non ci sono date di fine definite
                    $monthsPassed = ($interval->y * 12 + $interval->m);
                    $currentPeriod_months = $monthsPassed % $billingFrequency;
                    $currentInstallment = $currentPeriod_months;  // Ciclo infinito di periodi
                }
            }
        }

        // Calcolo le rate totali
        $totalInstallments = 1;
        if ($endDate_string) {
            $endDate = DateTime::createFromFormat('Y-m', $endDate_string);
            if ($endDate) {
                $totalInterval = $startDate->diff($endDate);
                if ($billingFrequency == 1) {
                    // Se la spesa ha una data di fine e $billingFrequency = 1
                    $totalInstallments = ($totalInterval->y * 12 + $totalInterval->m) + 1;
                } else {
                    // Se la spesa ha una data di fine e $billingFrequency > 1
                    $totalInstallments = $billingFrequency;
                }

                // Aggiungi controllo per impostare currentInstallment a 0 se endDate > relevantDate
                if ($endDate < $relevantDate) {
                    $currentInstallment = 0;
                }
            }
        } else {
            if ($billingFrequency > 1) {
                // Se la spesa non ha una data di fine e $billingFrequency > 1
                $totalInstallments = $billingFrequency;
            }
            // Se la spesa non ha una data di fine e $billingFrequency = 1, lascia $totalInstallments = 1
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

        // Calcolo l'ammontare sul salvadanaio delle spese ricorrenti
        if ($billingFrequency > 1 && ($endDate > $selectedDate || $undetermined == 1)) {
            $recurringSavings += $monthlyAmount * $currentInstallment;
        } else if ($billingFrequency = 1 && ($endDate > $selectedDate || $undetermined == 1)) {
            $recurringSavings += $monthlyAmount;
        }

        if($relevantDate > $startDate && ($endDate > $selectedDate || $undetermined == 1)){
            $thisMonthTotalRecurringExpenses += $monthlyAmount;
        }

        $recurringExpenses[] = [
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

    $thisMonthTotalRecurringExpenses = round($thisMonthTotalRecurringExpenses, 2);
    $recurringSavings = round($recurringSavings, 2);
    $stmt->close();
}


// Recupero tutte le spese stimate
$estimatedExpenses = [];
$sql = "SELECT id, name, amount, start_month, start_year, end_month, end_year, undetermined, debit_date, billing_frequency
        FROM estimated_expenses
        WHERE user_id = ?";

if ($stmt = $link->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($id, $name, $amount, $startMonth, $startYear, $endMonth, $endYear, $undetermined, $debitDate, $billingFrequency);
    while ($stmt->fetch()) {
        $amount = (float) $amount;
        $billingFrequency = (int) $billingFrequency;
        $monthlyAmount = $amount / $billingFrequency;  // Dividi l'importo per la frequenza di fatturazione

        $startDate_string = $startYear . '-' . $startMonth;
        $endDate_string = $endYear ? $endYear . '-' . $endMonth : null;
        $startDate = DateTime::createFromFormat('Y-m', $startDate_string);
        if (!$startDate) continue;

        // Determino se considerare la spesa per il mese corrente o il mese successivo
        if ($debitDate >= $salaryDate) {
            $relevantDate = $selectedDate;
        } else {
            $relevantDate = (clone $selectedDate)->modify('+1 month');
        }

        // Calcolo la rata corrente
        $interval = $startDate->diff($relevantDate);
        // Verifico se la data di inizio è nel futuro rispetto alla data rilevante
        if ($startDate > $relevantDate) {
            $currentInstallment = 0;
        } else {
            if ($billingFrequency == 1) {
                if ($endDate_string) {
                    // Se la frequenza di fatturazione è 1 e ci sono date di inizio e fine definite
                    $currentInstallment = ($interval->y * 12 + $interval->m) + 1;
                } else {
                    // Se la frequenza di fatturazione è 1 e non c'è una data di fine definita
                    $currentInstallment = 1;
                }
            } else {
                if ($endDate_string || $undetermined == 1) {
                    // Se la frequenza di fatturazione è maggiore di 1 e ci sono una data di fine o undetermined è 1
                    $monthsPassed = ($interval->y * 12 + $interval->m -1) + 1;
                    $totalPeriods = intdiv($monthsPassed, $billingFrequency);
                    $currentPeriod_months = $monthsPassed % $billingFrequency;
                    $currentInstallment = $currentPeriod_months + 1;  // Periodo corrente, esempio: 1, 2, 3, ... , n
                } else {
                    // Se la frequenza di fatturazione è maggiore di 1 e non ci sono date di fine definite
                    $monthsPassed = ($interval->y * 12 + $interval->m);
                    $currentPeriod_months = $monthsPassed % $billingFrequency;
                    $currentInstallment = $currentPeriod_months;  // Ciclo infinito di periodi
                }
            }
        }

        // Calcolo le rate totali
        $totalInstallments = 1;
        if ($endDate_string) {
            $endDate = DateTime::createFromFormat('Y-m', $endDate_string);
            if ($endDate) {
                $totalInterval = $startDate->diff($endDate);
                if ($billingFrequency == 1) {
                    // Se la spesa ha una data di fine e $billingFrequency = 1
                    $totalInstallments = ($totalInterval->y * 12 + $totalInterval->m) + 1;
                } else {
                    // Se la spesa ha una data di fine e $billingFrequency > 1
                    $totalInstallments = $billingFrequency;
                }

                // Aggiungi controllo per impostare currentInstallment a 0 se endDate > relevantDate
                if ($endDate < $relevantDate) {
                    $currentInstallment = 0;
                }
            }
        } else {
            if ($billingFrequency > 1) {
                // Se la spesa non ha una data di fine e $billingFrequency > 1
                $totalInstallments = $billingFrequency;
            }
            // Se la spesa non ha una data di fine e $billingFrequency = 1, lascia $totalInstallments = 1
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

        // Calcolo l'ammontare sul salvadanaio delle spese stimate
        if ($billingFrequency > 1 && ($endDate > $selectedDate || $undetermined == 1)) {
            $estimatedSavings += $monthlyAmount * $currentInstallment;
        } else if ($billingFrequency = 1 && ($endDate > $selectedDate || $undetermined == 1)) {
            $estimatedSavings += $monthlyAmount;
        }

        if($relevantDate > $startDate && ($endDate > $selectedDate || $undetermined == 1)){
            $thisMonthTotalEstimatedExpenses += $monthlyAmount;
        }

        $estimatedExpenses[] = [
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

    $thisMonthTotalEstimatedExpenses = round($thisMonthTotalEstimatedExpenses, 2);
    $estimatedSavings = round($estimatedSavings, 2);
    $stmt->close();
}

// Recupero le spese extra
$extraExpenses = [];
$sql = "SELECT id, name, amount, debit_date
        FROM extra_expenses
        WHERE user_id = ?";
if ($stmt = $link->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($id, $name, $amount, $debitDate);
    while ($stmt->fetch()) {
        $amount = (float) $amount; // Mi assicuro che $amount sia di tipo numerico
        $extraExpenses[] = [
            'id' => $id,
            'name' => $name,
            'amount' => $amount,
            'debit_date' => $debitDate
        ];
    }
    $stmt->close();
}

// Recupero le spese extra relative al mese corrente
$thismonthExtraExpenses = [];

if ($salaryDate) {
    // Salary date non nullo
    $startDate = (new DateTime())->setDate($selectedYear, $selectedMonth, $salaryDate);
    $endDate = (clone $startDate)->modify('+1 month')->modify('-1 day');
    $sql = "SELECT id, name, amount, debit_date
            FROM extra_expenses
            WHERE user_id = ? AND debit_date BETWEEN ? AND ?";
    if ($stmt = $link->prepare($sql)) {
        $startDate_str = $startDate->format('Y-m-d');
        $endDate_str = $endDate->format('Y-m-d');
        $stmt->bind_param("iss", $user_id, $startDate_str, $endDate_str);
        $stmt->execute();
        $stmt->bind_result($id, $name, $amount, $debitDate);
        while ($stmt->fetch()) {
            $amount = (float) $amount; // Mi assicuro che $amount sia di tipo numerico
            $thismonthExtraExpenses[] = [
                'id' => $id,
                'name' => $name,
                'amount' => $amount,
                'debit_date' => $debitDate
            ];
        }
        $stmt->close();
    }
} else {
    // Salary date nullo, considero il mese normalmente
    $sql = "SELECT id, name, amount, debit_date
            FROM extra_expenses
            WHERE user_id = ? AND MONTH(debit_date) = ? AND YEAR(debit_date) = ?";
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("iss", $user_id, $selectedMonth, $selectedYear);
        $stmt->execute();
        $stmt->bind_result($id, $name, $amount, $debitDate);
        while ($stmt->fetch()) {
            $amount = (float) $amount; // Mi assicuro che $amount sia di tipo numerico
            $thismonthExtraExpenses[] = [
                'id' => $id,
                'name' => $name,
                'amount' => $amount,
                'debit_date' => $debitDate
            ];
        }
        $stmt->close();
    }
}

// Calcolo il totale delle spese extra del mese in corso
$thisMonthTotalExtraExpenses = array_sum(array_column($thismonthExtraExpenses, 'amount'));

// Calcolo le spese totali come somma di quelle ricorrenti, stimate, extra e la somma messa nel salvadanaio per il mese corrente
$totalExpenses = round($thisMonthTotalRecurringExpenses + $thisMonthTotalEstimatedExpenses + $thisMonthTotalExtraExpenses, 2);

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
    $sql = "SELECT SUM(amount) as monthly_incomes FROM incomes WHERE user_id = ? AND DATE_FORMAT(added_date, '%Y-%m') = ?";
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("is", $user_id, $month_year);
        $stmt->execute();
        $stmt->bind_result($monthlyIncome);
        $stmt->fetch();
        $last_12_monthlyIncomes[] = $monthlyIncome ?: 0;
        $stmt->close();
    }

    // Recupero uscite per il mese corrente
    $monthlyExpense = 0;

    // Spese ricorrenti
    $sql = "SELECT amount, billing_frequency 
        FROM recurring_expenses 
        WHERE user_id = ? 
        AND (start_year < ? OR (start_year = ? AND start_month <= ?))
        AND (end_year IS NULL OR end_year > ? OR (end_year = ? AND end_month >= ?))";
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("iiiiiii", $user_id, $date->format('Y'), $date->format('Y'), $date->format('m'), $date->format('Y'), $date->format('Y'), $date->format('m'));
        $stmt->execute();
        $stmt->bind_result($amount, $billingFrequency);
        while ($stmt->fetch()) {
            $monthlyExpense += $amount / $billingFrequency;
        }
        $stmt->close();
    }

    // Spese stimate
    $sql = "SELECT amount, billing_frequency 
        FROM estimated_expenses 
        WHERE user_id = ? 
        AND (start_year < ? OR (start_year = ? AND start_month <= ?))
        AND (end_year IS NULL OR end_year > ? OR (end_year = ? AND end_month >= ?))";
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("iiiiiii", $user_id, $date->format('Y'), $date->format('Y'), $date->format('m'), $date->format('Y'), $date->format('Y'), $date->format('m'));
        $stmt->execute();
        $stmt->bind_result($amount, $billingFrequency);
        while ($stmt->fetch()) {
            $monthlyExpense += $amount / $billingFrequency;
        }
        $stmt->close();
    }

    // Spese extra
    if ($salaryDate) {
        // Salary date non nullo
        $startDate = (clone $date)->setDate($date->format('Y'), $date->format('m'), $salaryDate);
        $endDate = (clone $startDate)->modify('+1 month')->modify('-1 day');
        $sql = "SELECT SUM(amount) as total_extra FROM extra_expenses WHERE user_id = ? AND debit_date BETWEEN ? AND ?";
        if ($stmt = $link->prepare($sql)) {
            $startDate_str = $startDate->format('Y-m-d');
            $endDate_str = $endDate->format('Y-m-d');
            $stmt->bind_param("iss", $user_id, $startDate_str, $endDate_str);
            $stmt->execute();
            $stmt->bind_result($total_extra);
            $stmt->fetch();
            $monthlyExpense += $total_extra ?: 0;
            $stmt->close();
        }
    } else {
        // Salary date nullo, considero il mese normalmente
        $sql = "SELECT SUM(amount) as total_extra FROM extra_expenses WHERE user_id = ? AND MONTH(debit_date) = ? AND YEAR(debit_date) = ?";
        if ($stmt = $link->prepare($sql)) {
            $stmt->bind_param("iss", $user_id, $date->format('m'), $date->format('Y'));
            $stmt->execute();
            $stmt->bind_result($total_extra);
            $stmt->fetch();
            $monthlyExpense += $total_extra ?: 0;
            $stmt->close();
        }
    }

    $last_12_monthlyExpenses[] = $monthlyExpense;

    // Recupero l'importo salvadanaio per il mese corrente
    $sql = "SELECT SUM(amount) as monthly_piggy_bank FROM piggy_bank WHERE user_id = ? AND DATE_FORMAT(added_date, '%Y-%m') = ?";
    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param("is", $user_id, $month_year);
        $stmt->execute();
        $stmt->bind_result($monthly_piggy);
        $stmt->fetch();
        $monthlyPiggyBank[] = $monthly_piggy ?: 0;
        $stmt->close();
    }
}

// Inverto gli array per avere i mesi in ordine cronologico
$last_12_months = array_reverse($last_12_months);
$last_12_monthlyIncomes = array_reverse($last_12_monthlyIncomes);
$last_12_monthlyExpenses = array_reverse($last_12_monthlyExpenses);
$monthlyPiggyBank = array_reverse($monthlyPiggyBank);
?>
