<?php
session_start();
require_once '../config.php';
include 'retrieveData.php';
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Revisioni</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Centralized updated scripts (Bootstrap 5.3.3, jQuery, Popper, Chart.js, etc.) -->
    <?php include '../script.php'; ?>
    <!-- Bootstrap Icons (if not already included in /script.php) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../my.css" rel="stylesheet">
</head>
<body>
	<div class="content-wrapper">
		<div class="container mt-5">
			<h4>Revisioni</h4><br/>
			<?php foreach ($vehicles as $vehicle): ?>
				<div class="card mb-4">
					<div class="card-header d-flex justify-content-between align-items-center flex-wrap">
						<h4 class="mb-0">
							<?php echo htmlspecialchars($vehicle['description']); ?> (<?php echo htmlspecialchars($vehicle['plateNumber']); ?>)
						</h4>
						<button class="btn btn-primary add-revision-btn" 
							data-bs-toggle="modal" data-bs-target="#addRevisionModal" 
							data-vehicle-id="<?php echo $vehicle['id']; ?>">
							<i class="bi bi-plus"></i>
						</button>
					</div>
					<div class="card-body">
						<?php 
						$hasRevisions = false;
						if (isset($vehicleRevisions) && !empty($vehicleRevisions)): 
							foreach ($vehicleRevisions as $revision):
								if ($revision['vehicle_id'] == $vehicle['id']):
									$hasRevisions = true;
						?>
									<div class="card mb-3">
										<div class="card-body">
											<div class="row align-items-center">
												<div class="col">
													<p class="card-text mb-0">
														<strong>Costo:</strong> <?php echo htmlspecialchars($revision['amount']); ?><br>
														<strong>Data di esecuzione:</strong> <?php echo htmlspecialchars(formatDate($revision['buying_date'])); ?>
													</p>
												</div>
												<div class="col-auto">
													<button class="btn btn-warning btn-sm me-2"
															data-bs-toggle="modal"
															data-bs-target="#editRevisionModal"
															data-id="<?php echo $revision['id']; ?>"
															data-amount="<?php echo htmlspecialchars($revision['amount']); ?>"
															data-buying-date="<?php echo htmlspecialchars($revision['buying_date']); ?>">
														<i class="bi bi-pencil"></i>
													</button>
													<button class="btn btn-danger btn-sm"
															data-bs-toggle="modal"
															data-bs-target="#deleteRevisionModal"
															data-id="<?php echo $revision['id']; ?>">
														<i class="bi bi-trash"></i>
													</button>
												</div>
											</div>
										</div>
									</div>
						<?php 
								endif;
							endforeach; 
						endif; 
						?>
						<?php if (!$hasRevisions): ?>
							<div class="alert" role="alert">
								Nessuna revisione registrata per questo veicolo
							</div>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
    <?php include 'addRevisionModal.php'; ?>
    <?php include 'editRevisionModal.php'; ?>
    <?php include 'deleteRevisionModal.php'; ?>
    <?php include 'navbar.php'; ?>
    <?php include '../footer.php'; ?>
</body>
</html>
