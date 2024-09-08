<?php
session_start();
require_once '../config.php';
include 'retrieveData.php';

$active_expenses = [];
$expired_expenses = [];

$current_month = date('n');
$current_year = date('Y');

foreach ($recurringExpenses as $expense) {
    if ($expense['undetermined'] == 1 || $expense['end_year'] > $current_year || ($expense['end_year'] == $current_year && $expense['end_month'] >= $current_month)) {
        $active_expenses[] = $expense;
    } else {
        $expired_expenses[] = $expense;
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

usort($active_expenses, function($a, $b) use ($sort_by, $order) {
    return compare_expenses($a, $b, $sort_by, $order);
});
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Spese ricorrenti</title>
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
        <!-- Spese ricorrenti attive -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Spese ricorrenti attive</h4>
                <button class="btn btn-primary" data-toggle="modal" data-target="#addRecurringExpenseModal">
                    <i class="bi bi-plus"></i>
                </button>
            </div>
            <div class="card-body">
                <?php if (!empty($active_expenses)): ?>
                    <?php foreach ($active_expenses as $expense): ?>
                        <div class="card mb-4">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><?php echo htmlspecialchars($expense['name']); ?></h5>
                                <div>
                                    <button class="btn btn-warning" data-toggle="modal" data-target="#editRecurringExpenseModal" 
                                        data-id="<?php echo $expense['id']; ?>" 
                                        data-name="<?php echo htmlspecialchars($expense['name']); ?>" 
                                        data-amount="<?php echo htmlspecialchars($expense['amount']); ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-danger" data-toggle="modal" data-target="#deleteRecurringExpenseModal" 
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

        <!-- Spese ricorrenti scadute -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Spese ricorrenti scadute</h4>
                <button class="btn btn-primary toggle-content" data-toggle="collapse" data-target="#expiredExpensesContent" aria-expanded="false" aria-controls="expiredExpensesContent">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
            <div id="expiredExpensesContent" class="collapse card-body">
                <?php if (!empty($expired_expenses)): ?>
                    <?php foreach ($expired_expenses as $expense): ?>
                        <div class="card mb-4">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><?php echo htmlspecialchars($expense['name']); ?></h5>
                                <div>
                                    <button class="btn btn-warning" data-toggle="modal" data-target="#editRecurringExpenseModal" 
                                        data-id="<?php echo $expense['id']; ?>" 
                                        data-name="<?php echo htmlspecialchars($expense['name']); ?>" 
                                        data-amount="<?php echo htmlspecialchars($expense['amount']); ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-danger" data-toggle="modal" data-target="#deleteRecurringExpenseModal" 
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
    <?php include 'addRecurringExpenseModal.php'; ?>
    <?php include 'editRecurringExpenseModal.php'; ?>
    <?php include 'deleteRecurringExpenseModal.php'; ?>
	<?php include 'navbar.php'; ?>
    <?php include '../footer.php'; ?>

    <script>
        $('#editRecurringExpenseModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            var name = button.data('name');
            var amount = button.data('amount');
            var start_month = button.data('start-month');
            var start_year = button.data('start-year');
            var end_month = button.data('end-month');
            var end_year = button.data['end-year'];
            var undetermined = button.data('undetermined');
            var debit_date = button.data('debit-date');
            var billing_frequency = button.data('billing-frequency');

            var modal = $(this);
            modal.find('#edit_expense_id').val(id);
            modal.find('#edit_expense_name').val(name);
            modal.find('#edit_expense_amount').val(amount);
            modal.find('#edit_start_month').val(start_month);
            modal.find('#edit_start_year').val(start_year);
            modal.find('#edit_end_month').val(end_month);
            modal.find('#edit_end_year').val(end_year);
            modal.find('#edit_undetermined').prop('checked', undetermined);
            modal.find('#edit_debit_date').val(debit_date);
            modal.find('#edit_billing_frequency').val(billing_frequency);
        });

        $('#deleteRecurringExpenseModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');

            var modal = $(this);
            modal.find('#delete_expense_id').val(id);
        });
    </script>
</body>
</html>
