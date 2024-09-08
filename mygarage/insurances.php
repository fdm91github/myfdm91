<?php
session_start();
require_once '../config.php';
include 'retrieveData.php';
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Assicurazioni</title>
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
		<h4>Assicurazioni</h4><br/>
        <?php foreach ($vehicles as $vehicle): ?>
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <h4 class="mb-0"><?php echo htmlspecialchars($vehicle['description']); ?> (<?php echo htmlspecialchars($vehicle['plateNumber']); ?>)</h4>
                    <button class="btn btn-primary add-insurance-btn" data-toggle="modal" data-target="#addInsuranceModal" data-vehicle-id="<?php echo $vehicle['id']; ?>">
                        <i class="bi bi-plus"></i>
                    </button>
                </div>
                <div class="card-body">
                    <?php 
                    $hasInsurances = false;
                    if (isset($vehicleInsurances) && !empty($vehicleInsurances)): 
                        foreach ($vehicleInsurances as $insurance):
                            if ($insurance['vehicle_id'] == $vehicle['id']):
                                if (!$hasInsurances): 
                                    $hasInsurances = true; 
                    ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Compagnia</th>
                                                <th>Costo</th>
                                                <th>Data di acquisto</th>
                                                <th>Azioni</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                    <?php 
                                endif; 
                    ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($insurance['company']); ?></td>
                                                <td><?php echo htmlspecialchars($insurance['amount']); ?></td>
                                                <td><?php echo htmlspecialchars(formatDate($insurance['buying_date'])); ?></td>
                                                <td>
                                                    <button class="btn btn-warning btn-sm"
                                                            data-toggle="modal"
                                                            data-target="#editInsuranceModal"
                                                            data-id="<?php echo $insurance['id']; ?>"
                                                            data-company="<?php echo htmlspecialchars($insurance['company']); ?>"
                                                            data-amount="<?php echo htmlspecialchars($insurance['amount']); ?>"
                                                            data-buying-date="<?php echo htmlspecialchars($insurance['buying_date']); ?>">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-danger btn-sm"
                                                            data-toggle="modal"
                                                            data-target="#deleteInsuranceModal"
                                                            data-id="<?php echo $insurance['id']; ?>">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                    <?php 
                            endif;
                        endforeach; 
                    endif; 
                    ?>

                    <?php if ($hasInsurances): ?>
                                        </tbody>
                                    </table>
                                </div>
                    <?php else: ?>
                                <div class="alert" role="alert">
                                    Nessuna assicurazione registrata per questo veicolo
                                </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php include 'addInsuranceModal.php'; ?>
    <?php include 'editInsuranceModal.php'; ?>
    <?php include 'deleteInsuranceModal.php'; ?>
    <?php include 'navbar.php'; ?>
	<?php include '../footer.php'; ?>
	
</body>
</html>
