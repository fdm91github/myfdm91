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
	<div class="mb-3 d-flex justify-content-end">
	    <div class="col-md-4">
		<input type="text" id="searchExpenses" class="form-control" placeholder="Cerca una spesa...">
	    </div>
	</div>
        <!-- Spese extra attive -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Spese extra attive</h4>
                <button class="btn btn-primary" data-toggle="modal" data-target="#addExtraExpenseModal">
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
                                    <button class="btn btn-warning" data-toggle="modal" data-target="#editExtraExpenseModal"
                                        data-id="<?php echo $expense['id']; ?>" 
                                        data-name="<?php echo htmlspecialchars($expense['name']); ?>" 
                                        data-amount="<?php echo htmlspecialchars($expense['amount']); ?>" 
                                        data-date="<?php echo htmlspecialchars($expense['debit_date']); ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-danger" data-toggle="modal" data-target="#deleteExtraExpenseModal" 
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
                <button class="btn btn-primary toggle-content" data-toggle="collapse" data-target="#expiredExpensesContent" aria-expanded="false" aria-controls="expiredExpensesContent">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
            <div id="expiredExpensesContent" class="collapse card-body">
                <?php foreach ($expiredExpenses as $expense): ?>
                    <div class="card mb-4">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><?php echo htmlspecialchars($expense['name']); ?></h5>
                            <div>
                                <button class="btn btn-warning" data-toggle="modal" data-target="#editExtraExpenseModal"
                                    data-id="<?php echo $expense['id']; ?>" 
                                    data-name="<?php echo htmlspecialchars($expense['name']); ?>" 
                                    data-amount="<?php echo htmlspecialchars($expense['amount']); ?>" 
                                    data-date="<?php echo htmlspecialchars($expense['debit_date']); ?>">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-danger" data-toggle="modal" data-target="#deleteExtraExpenseModal" 
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
        $("#searchExpenses").on("keyup", function () {
            var value = $(this).val().toLowerCase();

            $(".card-body .card").each(function () {
                var expenseName = $(this).find("h5").text().toLowerCase();
                $(this).toggle(expenseName.includes(value));
            });
        });
    });
</script>

