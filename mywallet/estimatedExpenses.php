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
    <!-- Bootstrap JS e dipendenze varie -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <!-- Charts e grafici -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Custom CSS -->
	<link href="../my.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
		<div class="card mb-4">
			<div class="card-header d-flex justify-content-between align-items-center flex-wrap">
				<h4 class="mb-0">Spese stimate attive</h4>
				<button class="btn btn-primary btn-custom" data-toggle="modal" data-target="#addEstimatedExpenseModal">
					<i class="bi bi-plus"></i>
				</button>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-bordered">
						<thead>
							<tr>
								<th>
									<a href="?sort=name&order=<?php echo ($_GET['order'] ?? 'asc') === 'asc' ? 'desc' : 'asc'; ?>">
										Descrizione
										<?php if ($_GET['sort'] == 'name'): ?>
											<i class="bi bi-arrow-<?php echo ($_GET['order'] ?? 'asc') === 'asc' ? 'down' : 'up'; ?>"></i>
										<?php endif; ?>
									</a>
								</th>
								<th class="d-none d-md-table-cell">
									<a href="?sort=amount&order=<?php echo ($_GET['order'] ?? 'asc') === 'asc' ? 'desc' : 'asc'; ?>">
										Totale
										<?php if ($_GET['sort'] == 'amount'): ?>
											<i class="bi bi-arrow-<?php echo ($_GET['order'] ?? 'asc') === 'asc' ? 'down' : 'up'; ?>"></i>
										<?php endif; ?>
									</a>
								</th>
								<th>
									<a href="?sort=monthly_debit&order=<?php echo ($_GET['order'] ?? 'asc') === 'asc' ? 'desc' : 'asc'; ?>">
										Questo mese
										<?php if ($_GET['sort'] == 'monthly_debit'): ?>
											<i class="bi bi-arrow-<?php echo ($_GET['order'] ?? 'asc') === 'asc' ? 'down' : 'up'; ?>"></i>
										<?php endif; ?>
									</a>
								</th>
								<th class="d-none d-md-table-cell">
									<a href="?sort=next_debit_date&order=<?php echo ($_GET['order'] ?? 'asc') === 'asc' ? 'desc' : 'asc'; ?>">
										Prossimo addebito
										<?php if ($_GET['sort'] == 'next_debit_date'): ?>
											<i class="bi bi-arrow-<?php echo ($_GET['order'] ?? 'asc') === 'asc' ? 'down' : 'up'; ?>"></i>
										<?php endif; ?>
									</a>
								</th>
								<th class="d-none d-md-table-cell">
									<a href="?sort=billing_frequency&order=<?php echo ($_GET['order'] ?? 'asc') === 'asc' ? 'desc' : 'asc'; ?>">
										Frequenza
										<?php if ($_GET['sort'] == 'billing_frequency'): ?>
											<i class="bi bi-arrow-<?php echo ($_GET['order'] ?? 'asc') === 'asc' ? 'down' : 'up'; ?>"></i>
										<?php endif; ?>
									</a>
								</th>
								<th class="d-none d-md-table-cell">
									<a href="?sort=current_installment&order=<?php echo ($_GET['order'] ?? 'asc') === 'asc' ? 'desc' : 'asc'; ?>">
										Rata corrente
										<?php if ($_GET['sort'] == 'current_installment'): ?>
											<i class="bi bi-arrow-<?php echo ($_GET['order'] ?? 'asc') === 'asc' ? 'down' : 'up'; ?>"></i>
										<?php endif; ?>
									</a>
								</th>
								<th>Azioni</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($activeExpenses as $expense): ?>
								<tr>
									<td><?php echo htmlspecialchars($expense['name']); ?></td>
									<td class="d-none d-md-table-cell"><?php echo htmlspecialchars($expense['amount']); ?></td>
									<td><?php echo htmlspecialchars($expense['monthly_debit']); ?></td>
									<td class="d-none d-md-table-cell"><?php echo htmlspecialchars($expense['next_debit_date']); ?></td>
									<td class="d-none d-md-table-cell">
										<?php
										if($expense['billing_frequency'] > 1){
											echo 'Ogni ' . htmlspecialchars($expense['billing_frequency']) . ' mesi';
										} else {
											echo 'Ogni mese';
										}
										?>
									</td>
									<td class="d-none d-md-table-cell" align=center>
										<?php
											echo htmlspecialchars($expense['current_installment']) . ' di ' . htmlspecialchars($expense['total_installments']);
										?>
									</td>
									<td>
										<button class="btn btn-warning btn-sm" 
												data-toggle="modal" 
												data-target="#editEstimatedExpenseModal" 
												data-id="<?php echo $expense['id']; ?>" 
												data-name="<?php echo htmlspecialchars($expense['name']); ?>" 
												data-amount="<?php echo htmlspecialchars($expense['amount']); ?>" 
												data-start-month="<?php echo htmlspecialchars($expense['start_month']); ?>" 
												data-start-year="<?php echo htmlspecialchars($expense['start_year']); ?>" 
												data-end-month="<?php echo htmlspecialchars($expense['end_month']); ?>" 
												data-end-year="<?php echo htmlspecialchars($expense['end_year']); ?>" 
												data-undetermined="<?php echo $expense['undetermined']; ?>" 
												data-debit-date="<?php echo htmlspecialchars($expense['debit_date']); ?>" 
												data-billing-frequency="<?php echo htmlspecialchars($expense['billing_frequency']); ?>">
											<i class="bi bi-pencil"></i>
										</button>
										<button class="btn btn-danger btn-sm" 
												data-toggle="modal" 
												data-target="#deleteEstimatedExpenseModal" 
												data-id="<?php echo $expense['id']; ?>">
											<i class="bi bi-trash"></i>
										</button>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
        
        <?php if (!empty($expiredExpenses)): ?>
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <h4 class="mb-0">Spese stimate scadute</h4>
                <button style="color:white" class="btn btn-link btn-custom" data-toggle="collapse" data-target="#expiredExpensesContent" aria-expanded="false" aria-controls="expiredExpensesContent">
                    <i class="bi bi-plus"></i>
                </button>
            </div>
            <div id="expiredExpensesContent" class="collapse">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Descrizione</th>
                                    <th>Totale</th>
                                    <th class="d-none d-md-table-cell">Ultimo addebito</th>
                                    <th class="d-none d-md-table-cell">Frequenza</th>
                                    <th>Azioni</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($expiredExpenses as $expense): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($expense['name']); ?></td>
                                        <td><?php echo htmlspecialchars($expense['amount']); ?></td>
                                        <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($expense['last_debit_date']); ?></td>
                                        <td class="d-none d-md-table-cell">
                                            <?php
                                            if($expense['billing_frequency'] > 1){
                                                echo 'Ogni ' . htmlspecialchars($expense['billing_frequency']) . ' mesi';
                                            } else {
                                                echo 'Ogni mese';
                                            }
                                            ?>
                                        </td>
                                        <td align=center>
                                            <button class="btn btn-warning btn-sm" 
                                                    data-toggle="modal" 
                                                    data-target="#editEstimatedExpenseModal" 
                                                    data-id="<?php echo $expense['id']; ?>" 
                                                    data-name="<?php echo htmlspecialchars($expense['name']); ?>" 
                                                    data-amount="<?php echo htmlspecialchars($expense['amount']); ?>" 
                                                    data-start-month="<?php echo htmlspecialchars($expense['start_month']); ?>" 
                                                    data-start-year="<?php echo htmlspecialchars($expense['start_year']); ?>" 
                                                    data-end-month="<?php echo htmlspecialchars($expense['end_month']); ?>" 
                                                    data-end-year="<?php echo htmlspecialchars($expense['end_year']); ?>" 
                                                    data-undetermined="<?php echo $expense['undetermined']; ?>" 
                                                    data-debit-date="<?php echo htmlspecialchars($expense['debit_date']); ?>" 
                                                    data-billing-frequency="<?php echo htmlspecialchars($expense['billing_frequency']); ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-danger btn-sm" 
                                                    data-toggle="modal" 
                                                    data-target="#deleteEstimatedExpenseModal" 
                                                    data-id="<?php echo $expense['id']; ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php include 'addEstimatedExpenseModal.php'; ?>
    <?php include 'editEstimatedExpenseModal.php'; ?>
    <?php include 'deleteEstimatedExpenseModal.php'; ?>
    <?php include 'navbar.php'; ?>

    <script>
        $('#editEstimatedExpenseModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            var name = button.data('name');
            var amount = button.data('amount');
            var start_month = button.data('start-month');
            var start_year = button.data('start-year');
            var end_month = button.data('end-month');
            var end_year = button.data('end-year');
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

        $('#deleteEstimatedExpenseModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');

            var modal = $(this);
            modal.find('#delete_expense_id').val(id);
        });
    </script>
</body>
</html>
