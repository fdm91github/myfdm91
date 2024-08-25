<?php
session_start();
require_once '../config.php';
include 'retrieveData.php';
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Manutenzioni</title>
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
        <h4>Manutenzioni</h4><br/>
        <?php foreach ($vehicles as $vehicle): ?>
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <h4 class="mb-0"><?php echo htmlspecialchars($vehicle['description']); ?> (<?php echo htmlspecialchars($vehicle['plateNumber']); ?>)</h4>
                    <button class="btn btn-primary btn-custom add-service-btn" data-toggle="modal" data-target="#addServiceModal" data-vehicle-id="<?php echo $vehicle['id']; ?>">
                        <i class="bi bi-plus"></i>
                    </button>
                </div>
                <div class="card-body">
                    <?php 
                    $hasServices = false;
                    if (isset($vehicleServices) && !empty($vehicleServices)): 
                        foreach ($vehicleServices as $service):
                            if ($service['vehicle_id'] == $vehicle['id']):
                                if (!$hasServices): 
                                    $hasServices = true; 
                    ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Descrizione</th>
                                                <th>Costo</th>
                                                <th>Data di acquisto</th>
                                                <th>Eseguita a</th>
                                                <th>Azioni</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                    <?php 
                                endif; 
                    ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($service['description']); ?></td>
                                            <td><?php echo htmlspecialchars($service['amount']); ?></td>
                                            <td><?php echo htmlspecialchars(formatDate($service['buying_date'])); ?></td>
                                            <td><?php echo htmlspecialchars($service['registered_kilometers']); ?> km</td>
                                            <td>
                                                <button class="btn btn-warning btn-sm"
                                                        data-toggle="modal"
                                                        data-target="#editServiceModal"
                                                        data-id="<?php echo $service['id']; ?>"
                                                        data-description="<?php echo htmlspecialchars($service['description']); ?>"
                                                        data-amount="<?php echo htmlspecialchars($service['amount']); ?>"
                                                        data-buying-date="<?php echo htmlspecialchars($service['buying_date']); ?>"
                                                        data-registered-kilometers="<?php echo htmlspecialchars($service['registered_kilometers']); ?>">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-danger btn-sm"
                                                        data-toggle="modal"
                                                        data-target="#deleteServiceModal"
                                                        data-id="<?php echo $service['id']; ?>">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                                <?php if (!empty($service['attachment_path'])): ?>
                                                    <a href="<?php echo $service['attachment_path']; ?>" class="btn btn-success btn-sm" download>
                                                        <i class="bi bi-download"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <button class="btn btn-info btn-sm" type="button" data-toggle="collapse" data-target="#details-<?php echo $service['id']; ?>" aria-expanded="false" aria-controls="details-<?php echo $service['id']; ?>">
                                                    Dettagli
                                                </button>
                                            </td>
                                        </tr>
                                        <tr id="details-<?php echo $service['id']; ?>" class="collapse">
                                            <td colspan="5">
                                                <?php if (isset($serviceParts[$service['id']]) && !empty($serviceParts[$service['id']])): ?>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm">
                                                            <thead>
                                                                <tr>
                                                                    <th>Nome Parte</th>
                                                                    <th>Codice Parte</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($serviceParts[$service['id']] as $part): ?>
                                                                    <tr>
                                                                        <td><?php echo htmlspecialchars($part['part_name']); ?></td>
                                                                        <td><?php echo htmlspecialchars($part['part_number']); ?></td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="alert alert-info" role="alert">
                                                        Nessuna parte associata a questa manutenzione.
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                    <?php 
                            endif;
                        endforeach; 
                    endif; 
                    ?>

                    <?php if ($hasServices): ?>
                                        </tbody>
                                    </table>
                                </div>
                    <?php else: ?>
                                <div class="alert" role="alert">
                                    Nessuna manutenzione registrata per questo veicolo
                                </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php include 'addServiceModal.php'; ?>
    <?php include 'editServiceModal.php'; ?>
    <?php include 'deleteServiceModal.php'; ?>
    <?php include 'navbar.php'; ?>
    <?php include '../footer.php'; ?>
    
</body>
</html>
