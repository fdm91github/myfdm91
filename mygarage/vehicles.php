<?php
session_start();
require_once '../config.php';
include 'retrieveData.php';

// Inizializzo gli array per i veicoli attuali e passati
$activeVehicles = [];
$inactiveVehicles = [];

// Ottengo la data odierna
$currentDate = date('Y-m-d');

// Separo le spese attive da quelle scadute
foreach ($vehicles as $vehicle) {
    if (!$vehicle['deleted_at']) {
        $activeVehicles[] = $vehicle;
    } else {
        $inactiveVehicles[] = $vehicle;
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>I miei veicoli</title>
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
                <h4 class="mb-0">I miei veicoli</h4>
                <button class="btn btn-primary btn-custom" data-toggle="modal" data-target="#addVehicleModal">
                    <i class="bi bi-plus"></i>
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Descrizione</th>
                                <th>Targa</th>
                                <th>Data di acquisto</th>
                                <th>Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activeVehicles as $vehicle): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($vehicle['description']); ?></td>
                                    <td><?php echo htmlspecialchars($vehicle['plate_number']); ?></td>
                                    <td><?php echo htmlspecialchars($vehicle['buying_date']); ?></td>
                                    <td>
                                        <button class="btn btn-warning btn-sm"
                                                data-toggle="modal"
                                                data-target="#editExtraExpenseModal"
                                                data-id="<?php echo $vehicle['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($vehicle['description']); ?>"
                                                data-amount="<?php echo htmlspecialchars($vehicle['plate_number']); ?>"
                                                data-date="<?php echo htmlspecialchars($vehicle['buying_date']); ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm"
                                                data-toggle="modal"
                                                data-target="#deleteVehicleModal"
                                                data-id="<?php echo $vehicle['id']; ?>">
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
        <?php if (!empty($inactiveVehicles)): ?>
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <h4 class="mb-0">Veicoli non più attivi</h4>
                <button style="color:white" class="btn btn-link btn-custom" data-toggle="collapse" data-target="#inactiveVehiclesContent" aria-expanded="false" aria-controls="inactiveVehiclesContent">
                    <i class="bi bi-plus"></i>
                </button>
            </div>
            <div id="inactiveVehiclesContent" class="collapse">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
									<th>Descrizione</th>
									<th>Targa</th>
									<th>Data di acquisto</th>
									<th>Azioni</th>
								</tr>
                            </thead>
							<tbody>
								<?php foreach ($inactiveVehicles as $vehicle): ?>
									<tr>
										<td><?php echo htmlspecialchars($vehicle['description']); ?></td>
										<td><?php echo htmlspecialchars($vehicle['plate_number']); ?></td>
										<td><?php echo htmlspecialchars($vehicle['buying_date']); ?></td>
										<td>
											<button class="btn btn-warning btn-sm"
													data-toggle="modal"
													data-target="#editExtraExpenseModal"
													data-id="<?php echo $vehicle['id']; ?>"
													data-name="<?php echo htmlspecialchars($vehicle['description']); ?>"
													data-amount="<?php echo htmlspecialchars($vehicle['plate_number']); ?>"
													data-date="<?php echo htmlspecialchars($vehicle['buying_date']); ?>">
												<i class="bi bi-pencil"></i>
											</button>
											<button class="btn btn-danger btn-sm"
													data-toggle="modal"
													data-target="#deleteVehicleModal"
													data-id="<?php echo $vehicle['id']; ?>">
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
    <?php include 'addVehicleModal.php'; ?>
    <?php include 'editVehicleModal.php'; ?>
    <?php include 'deleteVehicleModal.php'; ?>
    <?php include 'navbar.php'; ?>
</body>
</html>
