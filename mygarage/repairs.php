<?php
session_start();
require_once '../config.php';
include 'retrieveData.php';

// Inizializzo gli array per i veicoli attuali e passati
$activeRepairings = [];
$inactiveRepairings = [];

// Ottengo la data odierna
$currentDate = date('Y-m-d');

// Separo le spese attive da quelle scadute
foreach ($repairings as $repairing) {
    if (!$repairing['deleted_at']) {
        $activeRepairings[] = $repairing;
    } else {
        $inactiveRepairings[] = $repairing;
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Riparazioni</title>
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
        <!-- Card per Veicoli Attivi -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <h4 class="mb-0">Riparazioni</h4>
                <button class="btn btn-primary btn-custom" data-toggle="modal" data-target="#addRepairingModal">
                    <i class="bi bi-plus"></i>
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Descrizione</th>
                                <th>Costo</th>
                                <th>Data</th>
                                <th>Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activeRepairings as $repairing): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($repairing['description']); ?></td>
                                    <td><?php echo htmlspecialchars($repairing['amount']); ?></td>
                                    <td><?php echo htmlspecialchars($repairing['date']); ?></td>
                                    <td>
                                        <button class="btn btn-warning btn-sm"
                                                data-toggle="modal"
                                                data-target="#editExtraExpenseModal"
                                                data-id="<?php echo $repairing['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($repairing['description']); ?>"
                                                data-amount="<?php echo htmlspecialchars($repairing['amount']); ?>"
                                                data-date="<?php echo htmlspecialchars($repairing['date']); ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm"
                                                data-toggle="modal"
                                                data-target="#deleteRepairingModal"
                                                data-id="<?php echo $repairing['id']; ?>">
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

        <!-- Card per Veicoli Inattivi -->
        <?php if (!empty($inactiveRepairings)): ?>
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <h4 class="mb-0">Veicoli non più attivi</h4>
                <button style="color:white" class="btn btn-link btn-custom" data-toggle="collapse" data-target="#inactiveRepairingsContent" aria-expanded="false" aria-controls="inactiveRepairingsContent">
                    <i class="bi bi-plus"></i>
                </button>
            </div>
            <div id="inactiveRepairingsContent" class="collapse">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
									<th>Descrizione</th>
									<th>Costo</th>
									<th>Data</th>
									<th>Azioni</th>
								</tr>
                            </thead>
							<tbody>
								<?php foreach ($activeRepairings as $repairing): ?>
									<tr>
										<td><?php echo htmlspecialchars($repairing['description']); ?></td>
										<td><?php echo htmlspecialchars($repairing['amount']); ?></td>
										<td><?php echo htmlspecialchars($repairing['date']); ?></td>
										<td>
											<button class="btn btn-warning btn-sm"
													data-toggle="modal"
													data-target="#editExtraExpenseModal"
													data-id="<?php echo $repairing['id']; ?>"
													data-name="<?php echo htmlspecialchars($repairing['description']); ?>"
													data-amount="<?php echo htmlspecialchars($repairing['amount']); ?>"
													data-date="<?php echo htmlspecialchars($repairing['date']); ?>">
												<i class="bi bi-pencil"></i>
											</button>
											<button class="btn btn-danger btn-sm"
													data-toggle="modal"
													data-target="#deleteRepairingModal"
													data-id="<?php echo $repairing['id']; ?>">
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
    <?php include 'addRepairingModal.php'; ?>
    <?php include 'editRepairingModal.php'; ?>
    <?php include 'deleteRepairingModal.php'; ?>
    <?php include 'navbar.php'; ?>
</body>
</html>
