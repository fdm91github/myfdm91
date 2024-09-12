<?php
session_start();
require_once '../config.php';
include 'retrieveData.php';

$activeExpenses = [];
$expiredExpenses = [];

$currentMonth = date('n');
$currentYear = date('Y');

foreach ($estimatedExpenses as $expense) {
    if ($expense['undetermined'] == 1 || $expense['end_year'] > $currentYear || ($expense['end_year'] == $currentYear && $expense['end_month'] >= $currentMonth)) {
        $activeExpenses[] = $expense;
    } else {
        $expiredExpenses[] = $expense;
    }
}

function compare_expenses($a, $b, $sort_by, $order) {
    if ($sort_by == 'amount' || $sort_by == 'monthly_debit' || $sort_by == 'billing_frequency' || $sort_by == 'current_installment') {
        $result = $a[$sort_by] <=> $b[$sort_by];
    } elseif ($sort_by == 'next_debit_date') {
        $date_a = DateTime::createFromFormat('d/m/Y', $a[$sort_by]);
        $date_b = DateTime::createFromFormat('d/m/Y', $b[$sort_by]);
        $result = $date_a <=> $date_b;
    } else {
        $result = strcmp($a[$sort_by], $b[$sort_by]);
    }

    return $order === 'desc' ? -$result : $result;
}

$sort_by = $_GET['sort'] ?? 'next_debit_date';
$order = $_GET['order'] ?? 'asc';

usort($activeExpenses, function($a, $b) use ($sort_by, $order) {
    return compare_expenses($a, $b, $sort_by, $order);
});
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Spese stimate</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../my.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <!-- Spese attive -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Spese stimate attive</h4>
                <button class="btn btn-primary" data-toggle="modal" data-target="#addEstimatedExpenseModal">
                    <i class="bi bi-plus"></i>
                </button>
            </div>
            <div class="card-body">
                <?php if (!empty($activeExpenses)): ?>
                    <?php foreach ($activeExpenses as $expense): ?>
                        <div class="card mb-4">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><?php echo htmlspecialchars($expense['name']); ?></h5>
                                <div>
                                    <button class="btn btn-warning" data-toggle="modal" data-target="#editEstimatedExpenseModal" 
										data-id="<?php echo $expense['id']; ?>"
										data-name="<?php echo htmlspecialchars($expense['name']); ?>"
										data-amount="<?php echo htmlspecialchars($expense['amount']); ?>"
										data-start-month="<?php echo $expense['start_month']; ?>"
										data-start-year="<?php echo $expense['start_year']; ?>"
										data-end-month="<?php echo $expense['end_month']; ?>"
										data-end-year="<?php echo $expense['end_year']; ?>"
										data-undetermined="<?php echo $expense['undetermined']; ?>"
										data-debit-date="<?php echo $expense['debit_date']; ?>"
										data-billing-frequency="<?php echo $expense['billing_frequency']; ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-danger" data-toggle="modal" data-target="#deleteEstimatedExpenseModal" 
                                        data-id="<?php echo $expense['id']; ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <p><strong>Totale:</strong> €<?php echo htmlspecialchars($expense['amount']); ?></p>
                                <p><strong>Questo mese:</strong> €<?php echo htmlspecialchars($expense['monthly_debit']); ?></p>
                                <p><strong>Prossimo addebito:</strong> <?php echo htmlspecialchars($expense['next_debit_date']); ?></p>
                                <p><strong>Frequenza:</strong> 
                                    <?php 
                                    if ($expense['billing_frequency'] > 1) {
                                        echo 'Ogni ' . htmlspecialchars($expense['billing_frequency']) . ' mesi';
                                    } else {
                                        echo 'Ogni mese';
                                    }
                                    ?>
                                </p>
                                <p><strong>Rata corrente:</strong> <?php echo htmlspecialchars($expense['current_installment']) . ' di ' . htmlspecialchars($expense['total_installments']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-info" role="alert">
                        Nessuna spesa attiva trovata.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Spese scadute -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Spese stimate scadute</h4>
                <button class="btn btn-primary toggle-content" data-toggle="collapse" data-target="#expiredExpensesContent" aria-expanded="false" aria-controls="expiredExpensesContent">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
            <div id="expiredExpensesContent" class="collapse card-body">
                <?php if (!empty($expiredExpenses)): ?>
                    <?php foreach ($expiredExpenses as $expense): ?>
                        <div class="card mb-4">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><?php echo htmlspecialchars($expense['name']); ?></h5>
                                <div>
                                    <button class="btn btn-warning" data-toggle="modal" data-target="#editEstimatedExpenseModal" 
										data-id="<?php echo $expense['id']; ?>"
										data-name="<?php echo htmlspecialchars($expense['name']); ?>"
										data-amount="<?php echo htmlspecialchars($expense['amount']); ?>"
										data-start-month="<?php echo $expense['start_month']; ?>"
										data-start-year="<?php echo $expense['start_year']; ?>"
										data-end-month="<?php echo $expense['end_month']; ?>"
										data-end-year="<?php echo $expense['end_year']; ?>"
										data-undetermined="<?php echo $expense['undetermined']; ?>"
										data-debit-date="<?php echo $expense['debit_date']; ?>"
										data-billing-frequency="<?php echo $expense['billing_frequency']; ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-danger" data-toggle="modal" data-target="#deleteEstimatedExpenseModal" 
                                        data-id="<?php echo $expense['id']; ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <p><strong>Totale:</strong> €<?php echo htmlspecialchars($expense['amount']); ?></p>
                                <p><strong>Ultimo addebito:</strong> <?php echo htmlspecialchars($expense['last_debit_date']); ?></p>
                                <p><strong>Frequenza:</strong> 
                                    <?php 
                                    if ($expense['billing_frequency'] > 1) {
                                        echo 'Ogni ' . htmlspecialchars($expense['billing_frequency']) . ' mesi';
                                    } else {
                                        echo 'Ogni mese';
                                    }
                                    ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-info" role="alert">
                        Nessuna spesa scaduta trovata.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <?php include 'addEstimatedExpenseModal.php'; ?>
    <?php include 'editEstimatedExpenseModal.php'; ?>
    <?php include 'deleteEstimatedExpenseModal.php'; ?>
    <?php include 'navbar.php'; ?>
    <?php include '../footer.php'; ?>

</body>
</html>
