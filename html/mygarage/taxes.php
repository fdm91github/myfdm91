<?php
session_start();
require_once '../config.php';
include 'retrieveData.php';
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Bolli</title>
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
			<h4>Bolli</h4><br/>
			<?php foreach ($vehicles as $vehicle): ?>
				<div class="card mb-4">
					<div class="card-header d-flex justify-content-between align-items-center flex-wrap">
						<h4 class="mb-0">
							<?php echo htmlspecialchars($vehicle['description']); ?> (<?php echo htmlspecialchars($vehicle['plateNumber']); ?>)
						</h4>
						<button class="btn btn-primary add-tax-btn" 
							data-bs-toggle="modal" data-bs-target="#addTaxModal" 
							data-vehicle-id="<?php echo $vehicle['id']; ?>">
							<i class="bi bi-plus"></i>
						</button>
					</div>
					<div class="card-body">
						<?php 
						$hasTaxes = false;
						if (isset($vehicleTaxes) && !empty($vehicleTaxes)):
							foreach ($vehicleTaxes as $tax):
								if ($tax['vehicle_id'] == $vehicle['id']):
									$hasTaxes = true;
						?>
									<div class="card mb-3">
										<div class="card-body">
											<div class="row align-items-center">
												<div class="col">
													<p class="card-text mb-0">
														<strong>Costo:</strong> <?php echo htmlspecialchars($tax['amount']); ?><br>
														<strong>Data del pagamento:</strong> <?php echo htmlspecialchars(formatDate($tax['buying_date'])); ?>
													</p>
												</div>
												<div class="col-auto">
													<button class="btn btn-warning btn-sm me-2"
															data-bs-toggle="modal"
															data-bs-target="#editTaxModal"
															data-id="<?php echo $tax['id']; ?>"
															data-amount="<?php echo htmlspecialchars($tax['amount']); ?>"
															data-buying-date="<?php echo htmlspecialchars($tax['buying_date']); ?>">
														<i class="bi bi-pencil"></i>
													</button>
													<button class="btn btn-danger btn-sm"
															data-bs-toggle="modal"
															data-bs-target="#deleteTaxModal"
															data-id="<?php echo $tax['id']; ?>">
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
						<?php if (!$hasTaxes): ?>
							<div class="alert" role="alert">
								Nessun bollo registrato per questo veicolo
							</div>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
    <?php include 'addTaxModal.php'; ?>
    <?php include 'editTaxModal.php'; ?>
    <?php include 'deleteTaxModal.php'; ?>
    <?php include 'navbar.php'; ?>
    <?php include '../footer.php'; ?>
</body>
</html>
