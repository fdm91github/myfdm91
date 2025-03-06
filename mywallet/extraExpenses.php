<?php
session_start();
require_once '../config.php';
include 'retrieveData.php';

// Inizializzo gli array per le spese attive e scadute
$activeExpenses = [];
$expiredExpenses = [];

// Ottengo la data odierna
$currentDate = date('Y-m-d');

// Separo le spese attive da quelle scadute
foreach ($extraExpenses as $expense) {
    if ($expense['debit_date'] >= $currentDate) {
        $activeExpenses[] = $expense;
    } else {
        $expiredExpenses[] = $expense;
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Spese extra</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Centralized updated scripts (Bootstrap 5.3.3, jQuery, Popper, etc.) -->
    <?php include '../script.php'; ?>
    <!-- Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../my.css" rel="stylesheet">
</head>
<body>
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
                            <label for="searchExpenses">Nome:</label>
                            <input type="text" id="searchExpenses" class="form-control" placeholder="Cerca una spesa...">
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
        
        <!-- Spese extra attive -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Spese extra attive</h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExtraExpenseModal">
                    <i class="bi bi-plus"></i>
                </button>
            </div>
            <div class="card-body">
                <?php if (!empty($activeExpenses)): ?>
                    <?php foreach ($activeExpenses as $expense): ?>
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><?php echo htmlspecialchars($expense['name']); ?></h5>
                                <div>
                                    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editExtraExpenseModal"
                                        data-id="<?php echo $expense['id']; ?>" 
                                        data-name="<?php echo htmlspecialchars($expense['name']); ?>" 
                                        data-amount="<?php echo htmlspecialchars($expense['amount']); ?>" 
                                        data-date="<?php echo htmlspecialchars($expense['debit_date']); ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteExtraExpenseModal" 
                                        data-id="<?php echo $expense['id']; ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <p><strong>Totale:</strong> €<?php echo htmlspecialchars($expense['amount']); ?></p>
                                <p><strong>Data di addebito:</strong> <?php echo htmlspecialchars(date('d/m/Y', strtotime($expense['debit_date']))); ?></p>
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

        <!-- Spese extra scadute -->
        <?php if (!empty($expiredExpenses)): ?>
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Spese extra scadute</h4>
                <button class="btn btn-primary toggle-content" data-bs-toggle="collapse" data-bs-target="#expiredExpensesContent" aria-expanded="false" aria-controls="expiredExpensesContent">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
            <div id="expiredExpensesContent" class="collapse card-body">
                <?php foreach ($expiredExpenses as $expense): ?>
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><?php echo htmlspecialchars($expense['name']); ?></h5>
                            <div>
                                <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editExtraExpenseModal"
                                    data-id="<?php echo $expense['id']; ?>" 
                                    data-name="<?php echo htmlspecialchars($expense['name']); ?>" 
                                    data-amount="<?php echo htmlspecialchars($expense['amount']); ?>" 
                                    data-date="<?php echo htmlspecialchars($expense['debit_date']); ?>">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteExtraExpenseModal" 
                                    data-id="<?php echo $expense['id']; ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <p><strong>Totale:</strong> €<?php echo htmlspecialchars($expense['amount']); ?></p>
                            <p><strong>Data di addebito:</strong> <?php echo htmlspecialchars(date('d/m/Y', strtotime($expense['debit_date']))); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Modals -->
    <?php include 'addExtraExpenseModal.php'; ?>
    <?php include 'editExtraExpenseModal.php'; ?>
    <?php include 'deleteExtraExpenseModal.php'; ?>
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
    });
</script>
