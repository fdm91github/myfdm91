<?php
session_start();
require_once '../config.php';
include 'retrieveData.php';

// Inizializzo gli array per i veicoli attuali e passati
$activeVehicles = [];
$inactiveVehicles = [];

// Ottengo la data odierna
$currentDate = date('Y-m-d');

// Separo i veicoli attivi da quelli scaduti
foreach ($vehicles as $vehicle) {
    if (!$vehicle['deletedAt']) {
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
    <style>
        .plate {
            display: inline-block;
            width: 150px;
            height: 40px;
            background-color: #ffffff;
            border: 1px solid #000;
            position: relative;
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            line-height: 40px;
        }

        .plate::before, .plate::after {
            content: '';
            position: absolute;
            top: 0;
            width: 20px;
            height: 100%;
            background-color: #003399;
        }

        .plate::before {
            left: 0;
            background-image: url('path_to_left_flag_image'); /* Add your country flag image */
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }

        .plate::after {
            right: 0;
        }

        .plate-number {
            display: inline-block;
            width: calc(100% - 40px);
            height: 100%;
            background-color: #ffffff;
            color: #000;
            line-height: 40px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <!-- Card per Veicoli Attivi -->
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <h4 class="mb-0">I miei veicoli</h4>
                <button class="btn btn-primary" data-toggle="modal" data-target="#addVehicleModal">
                    <i class="bi bi-plus"></i>
                </button>
            </div>
            <div class="row mt-3">
                <?php foreach ($activeVehicles as $vehicle): ?>
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($vehicle['description']); ?></h5>
                                <div class="plate">
                                    <span class="plate-number"><?php echo htmlspecialchars($vehicle['plateNumber']); ?></span>
                                </div>
                                <p class="card-text mt-3">
									<h5>Dati veicolo</h5>
                                    <strong>Nr. di telaio:</strong> <?php echo htmlspecialchars($vehicle['chassisNumber']); ?><br>
									<strong>Data di acquisto:</strong> <?php echo htmlspecialchars(formatDate($vehicle['buyingDate'])); ?><br>
									<br/><h5>Scadenze</h5>
                                    <strong>Scadenza assicurazione:</strong> <?php echo htmlspecialchars(formatDate($vehicle['nextInsuranceExpirationDate'])); ?><br>
                                    <strong>Scadenza bollo:</strong> <?php echo htmlspecialchars(formatDate($vehicle['nextTaxExpirationDate'])); ?><br>
                                    <strong>Scadenza revisione:</strong> <?php echo htmlspecialchars(formatDate($vehicle['nextRevisionExpirationDate'])); ?><br>
                                </p>
                                <button class="btn btn-warning btn-sm"
										data-toggle="modal"
										data-target="#editVehicleModal"
										data-id="<?php echo $vehicle['id']; ?>"
										data-description="<?php echo htmlspecialchars($vehicle['description']); ?>"
										data-buying-date="<?php echo htmlspecialchars($vehicle['buyingDate']); ?>"
										data-plate-number="<?php echo htmlspecialchars($vehicle['plateNumber']); ?>"
										data-chassis-number="<?php echo htmlspecialchars($vehicle['chassisNumber']); ?>"
										data-tax-month="<?php echo htmlspecialchars($vehicle['taxMonth']); ?>"
										data-revision-month="<?php echo htmlspecialchars($vehicle['revisionMonth']); ?>"
										data-insurance-expiration-date="<?php echo htmlspecialchars($vehicle['nextInsuranceExpirationDate']); ?>">
									<i class="bi bi-pencil"></i>
								</button>
                                <button class="btn btn-danger btn-sm"
                                        data-toggle="modal"
                                        data-target="#deleteVehicleModal"
                                        data-id="<?php echo $vehicle['id']; ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Card per Veicoli Inattivi -->
        <?php if (!empty($inactiveVehicles)): ?>
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <h4 class="mb-0">Veicoli non più attivi</h4>
                <button style="color:white" class="btn btn-link" data-toggle="collapse" data-target="#inactiveVehiclesContent" aria-expanded="false" aria-controls="inactiveVehiclesContent">
                    <i class="bi bi-plus"></i>
                </button>
            </div>
            <div id="inactiveVehiclesContent" class="collapse">
                <div class="row mt-3">
                    <?php foreach ($inactiveVehicles as $vehicle): ?>
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($vehicle['description']); ?></h5>
                                    <div class="plate">
                                        <span class="plate-number"><?php echo htmlspecialchars($vehicle['plateNumber']); ?></span>
                                    </div>
                                <p class="card-text mt-3">
                                    <strong>Data di acquisto:</strong> <?php echo htmlspecialchars($vehicle['buyingDate']); ?><br>
                                    <strong>Scadenza assicurazione:</strong> <?php echo htmlspecialchars($vehicle['nextInsuranceExpirationDate']); ?><br>
                                    <strong>Scadenza bollo:</strong> <?php echo htmlspecialchars($vehicle['nextTaxExpirationDate']); ?><br>
                                    <strong>Scadenza revisione:</strong> <?php echo htmlspecialchars($vehicle['nextRevisionExpirationDate']); ?><br>
									<strong>Data eliminazione:</strong> <?php echo htmlspecialchars(formatDate($vehicle['deletedAt'])); ?><br>
                                </p>
                                    <button class="btn btn-success btn-sm"
                                            data-toggle="modal"
                                            data-target="#restoreVehicleModal"
                                            data-id="<?php echo $vehicle['id']; ?>">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm"
                                            data-toggle="modal"
                                            data-target="#permanentlyDeleteVehicleModal"
                                            data-id="<?php echo $vehicle['id']; ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php include 'addVehicleModal.php'; ?>
    <?php include 'editVehicleModal.php'; ?>
    <?php include 'deleteVehicleModal.php'; ?>
	<?php include 'permanentlyDeleteVehicleModal.php'; ?>
	<?php include 'restoreVehicleModal.php'; ?>
    <?php include 'navbar.php'; ?>
	<?php include '../footer.php'; ?>
	
</body>
</html>
