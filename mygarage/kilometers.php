<?php
session_start();
require_once '../config.php';
include 'retrieveData.php';
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Percorrenza</title>
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
		<h4>Percorrenza</h4><br/>
        <?php foreach ($vehicles as $vehicle): ?>
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <h4 class="mb-0"><?php echo htmlspecialchars($vehicle['description']); ?> (<?php echo htmlspecialchars($vehicle['plateNumber']); ?>)</h4>
                    <button class="btn btn-primary btn-custom add-kilometers-btn" data-toggle="modal" data-target="#addKilometersModal" data-vehicle-id="<?php echo $vehicle['id']; ?>">
                        <i class="bi bi-plus"></i>
                    </button>
                </div>
                <div class="card-body">
                    <?php 
                    $hasKilometers = false;
                    if (isset($vehicleKilometers) && !empty($vehicleKilometers)): 
                        foreach ($vehicleKilometers as $kilometers):
                            if ($kilometers['vehicle_id'] == $vehicle['id']):
                                if (!$hasKilometers): 
                                    $hasKilometers = true; 
                    ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Kilometri</th>
                                                <th>Registrati il</th>
                                                <th>Azioni</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                    <?php 
                                endif; 
                    ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($kilometers['kilometers']); ?></td>
                                                <td><?php echo htmlspecialchars(formatDate($kilometers['date'])); ?></td>
                                                <td>
                                                    <button class="btn btn-warning btn-sm"
                                                            data-toggle="modal"
                                                            data-target="#editKilometersModal"
                                                            data-id="<?php echo $kilometers['id']; ?>"
                                                            data-kilometers="<?php echo htmlspecialchars($kilometers['kilometers']); ?>"
                                                            data-date="<?php echo htmlspecialchars($kilometers['date']); ?>">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-danger btn-sm"
                                                            data-toggle="modal"
                                                            data-target="#deleteKilometersModal"
                                                            data-id="<?php echo $kilometers['id']; ?>">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                    <?php 
                            endif;
                        endforeach; 
                    endif; 
                    ?>

                    <?php if ($hasKilometers): ?>
                                        </tbody>
                                    </table>
                                </div>
                    <?php else: ?>
                                <div class="alert" role="alert">
                                    Nessuna percorrenza registrata per questo veicolo
                                </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php include 'addKilometersModal.php'; ?>
    <?php include 'editKilometersModal.php'; ?>
    <?php include 'deleteKilometersModal.php'; ?>
    <?php include 'navbar.php'; ?>
	<?php include '../footer.php'; ?>
	
</body>
</html>
