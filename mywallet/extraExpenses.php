<?php
session_start();
require_once 'config.php';
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
        <!-- Card per Spese extra attive -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <h4 class="mb-0">Spese extra attive</h4>
                <button class="btn btn-primary btn-custom" data-toggle="modal" data-target="#addExtraExpenseModal">
                    <i class="bi bi-plus"></i>
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Descrizione</th>
                                <th>Totale</th>
                                <th>Data di addebito</th>
                                <th>Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activeExpenses as $expense): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($expense['name']); ?></td>
                                    <td><?php echo htmlspecialchars($expense['amount']); ?></td>
                                    <td><?php echo htmlspecialchars($expense['debit_date']); ?></td>
                                    <td>
                                        <button class="btn btn-warning btn-sm"
                                                data-toggle="modal"
                                                data-target="#editExtraExpenseModal"
                                                data-id="<?php echo $expense['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($expense['name']); ?>"
                                                data-amount="<?php echo htmlspecialchars($expense['amount']); ?>"
                                                data-date="<?php echo htmlspecialchars($expense['debit_date']); ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm"
                                                data-toggle="modal"
                                                data-target="#deleteExtraExpenseModal"
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

        <!-- Card per Spese extra scadute -->
        <?php if (!empty($expiredExpenses)): ?>
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <h4 class="mb-0">Spese extra scadute</h4>
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
                                    <th>Data di addebito</th>
                                    <th>Azioni</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($expiredExpenses as $expense): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($expense['name']); ?></td>
                                        <td><?php echo htmlspecialchars($expense['amount']); ?></td>
                                        <td><?php echo htmlspecialchars($expense['debit_date']); ?></td>
                                        <td>
                                            <button class="btn btn-warning btn-sm"
                                                    data-toggle="modal"
                                                    data-target="#editExtraExpenseModal"
                                                    data-id="<?php echo $expense['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($expense['name']); ?>"
                                                    data-amount="<?php echo htmlspecialchars($expense['amount']); ?>"
                                                    data-date="<?php echo htmlspecialchars($expense['debit_date']); ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-danger btn-sm"
                                                    data-toggle="modal"
                                                    data-target="#deleteExtraExpenseModal"
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
    <?php include 'addExtraExpenseModal.php'; ?>
    <?php include 'editExtraExpenseModal.php'; ?>
    <?php include 'deleteExtraExpenseModal.php'; ?>
    <?php include 'navbar.php'; ?>
</body>
</html>
