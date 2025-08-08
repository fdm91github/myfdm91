<?php
session_start();
require_once '../config.php';
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Spese ricorrenti</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include '../script.php'; ?>
    <link href="../my.css" rel="stylesheet">
</head>
<body>
	<?php
		include 'retrieveData.php';
		
		$activeExpenses = [];
		$expiredExpenses = [];

		$currentMonth = date('n');
		$currentYear = date('Y');

		foreach ($recurringExpenses as $expense) {
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
	<div class="content-wrapper">
		<div class="container mt-5">
			<!-- Filter Card -->
			<div class="card mb-4">
				<div class="card-header d-flex justify-content-between align-items-center">
					<h4 class="mb-0">Filtri</h4>
					<button class="btn btn-primary toggle-content" data-bs-toggle="collapse" data-bs-target="#filterContent" aria-expanded="false" aria-controls="filterContent">
						<i class="bi bi-funnel"></i>
					</button>
				</div>
				<div id="filterContent" class="collapse card-body">
					<div class="mb-3">
						<div class="row mb-2">
							<div class="col-md-6">
								<label for="searchExpenses">Descrizione:</label>
								<input type="text" id="searchExpenses" class="form-control" placeholder="Cerca un elemento...">
							</div>
							<div class="col-md-6">
								<label for="expenseFilter">Tipo spesa:</label>
								<select id="expenseFilter" class="form-control">
									<option value="both" selected>Tutte</option>
									<option value="active">Attive</option>
									<option value="expired">Scadute</option>
								</select>
							</div>
						</div>
						<div class="row mb-2">
							<div class="col-md-6">
								<label for="minPrice">Min. Totale:</label>
								<input type="number" id="minPrice" class="form-control" min="0" step="0.01">
							</div>
							<div class="col-md-6">
								<label for="maxPrice">Max. Totale:</label>
								<input type="number" id="maxPrice" class="form-control" min="0" step="0.01">
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Spese ricorrenti attive -->
			<div class="card mb-4">
				<div class="card-header d-flex justify-content-between align-items-center">
					<h4 class="mb-0">Spese ricorrenti attive</h4>
					<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRecurringExpenseModal">
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
										<button class="btn btn-warning btn-sm me-2" data-bs-toggle="modal" data-bs-target="#editRecurringExpenseModal"
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
										<button class="btn btn-danger btn-sm me-2" data-bs-toggle="modal" data-bs-target="#deleteRecurringExpenseModal" 
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
									<?php if ($expense['total_installments']>1): ?>
										<p><strong>Rata corrente:</strong> <?php echo htmlspecialchars($expense['current_installment']) . ' di ' . htmlspecialchars($expense['total_installments']); ?></p>
									<?php endif; ?>
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
					<button class="btn btn-primary toggle-content" data-bs-toggle="collapse" data-bs-target="#expiredExpensesContent" aria-expanded="false" aria-controls="expiredExpensesContent">
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
										<button class="btn btn-warning btn-sm me-2" data-bs-toggle="modal" data-bs-target="#editRecurringExpenseModal"
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
										<button class="btn btn-danger btn-sm me-2" data-bs-toggle="modal" data-bs-target="#deleteRecurringExpenseModal" 
											data-id="<?php echo $expense['id']; ?>">
											<i class="bi bi-trash"></i>
										</button>
									</div>
								</div>
								<div class="card-body">
									<p><strong>Totale:</strong> €<?php echo htmlspecialchars($expense['amount']); ?></p>
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
	</div>
    <!-- Modals -->
    <?php include 'addRecurringExpenseModal.php'; ?>
    <?php include 'editRecurringExpenseModal.php'; ?>
    <?php include 'deleteRecurringExpenseModal.php'; ?>
    <?php include 'navbar.php'; ?>
    <?php include '../footer.php'; ?>
</body>
</html>

<script>
    $(document).ready(function () {
        function filterExpenses() {
            let searchValue = $("#searchExpenses").val().toLowerCase();
            let minPrice = parseFloat($("#minPrice").val()) || 0;
            let maxPrice = parseFloat($("#maxPrice").val()) || Infinity;
            let expenseFilter = $("#expenseFilter").val();

            $(".card.mb-4").each(function () {
                let isFilterCard = $(this).find("h4").text().includes("Filtri"); // Keep filter card visible
                let isActive = $(this).find("h4").text().toLowerCase().includes("attive");
                let isExpired = $(this).find("h4").text().toLowerCase().includes("scadute");

                let matchesFilter = isFilterCard || (expenseFilter === "both") ||
                    (expenseFilter === "active" && isActive) ||
                    (expenseFilter === "expired" && isExpired);

                $(this).toggle(matchesFilter);
            });

            $(".card-body .card").each(function () {
                let expenseName = $(this).find("h5").text().toLowerCase();
                let priceText = $(this).find("p:contains('Totale:')").text().replace('Totale:', '').replace('€', '').trim();
                let price = parseFloat(priceText);

                let matchesSearch = expenseName.includes(searchValue);
                let matchesPrice = price >= minPrice && price <= maxPrice;

                $(this).toggle(matchesSearch && matchesPrice);
            });
        }

        $("#searchExpenses, #minPrice, #maxPrice, #expenseFilter").on("input change", function () {
            filterExpenses();
            // Use Bootstrap 5 Collapse instance to show filter content
            var collapseEl = document.getElementById("filterContent");
            var bsCollapse = new bootstrap.Collapse(collapseEl, {toggle: false});
            bsCollapse.show();
        });
		
		// Select the collapse element and its toggle button
        var expiredCollapse = $('#expiredExpensesContent');
        var toggleButton = $('button[data-bs-target="#expiredExpensesContent"]');

        // When the collapse is shown, change the icon to bi-eye-slash
        expiredCollapse.on('shown.bs.collapse', function () {
            toggleButton.find('i').removeClass('bi-eye').addClass('bi-eye-slash');
        });

        // When the collapse is hidden, revert the icon to bi-eye
        expiredCollapse.on('hidden.bs.collapse', function () {
            toggleButton.find('i').removeClass('bi-eye-slash').addClass('bi-eye');
        });
		
    });
</script>
